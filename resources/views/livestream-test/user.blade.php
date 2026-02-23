@extends('layouts.livestream-test')

@section('title', 'User Livestream Test')

@section('content')
<div>
    <h1 class="test-page-title">User Livestream Test</h1>
    <p class="test-page-desc">Same flow as the app: list live streams, get credentials from API, join via Agora. Video appears when a host is publishing (e.g. OBS via Admin → Broadcast).</p>
</div>

@if(config('services.livestream.local_test', false))
<div class="test-card border-amber-500/30 bg-amber-500/5">
    <p class="text-sm text-amber-200"><strong>Local test</strong> — No API token needed. Server uses <code class="bg-black/30 px-1 rounded">test-live</code> and <code class="bg-black/30 px-1 rounded">test-credentials</code>. Ensure <code class="bg-black/30 px-1 rounded">AGORA_APP_CERTIFICATE</code> is set so a token is returned.</p>
</div>
@endif

{{-- API Token (for app-like flow when not in local test) --}}
<div class="test-card">
    <label class="block text-sm font-medium text-[var(--meta-text-secondary)] mb-2">API Token (Bearer)</label>
    <div class="flex gap-2">
        <input type="password" id="apiToken" placeholder="Optional: paste token from POST /api/login" class="test-input flex-1">
        <button type="button" id="setToken" class="test-btn-ghost shrink-0">Set Token</button>
    </div>
    <p class="text-xs text-[var(--meta-text-muted)] mt-1">With token: uses <code>GET /livestreams/live</code> and <code>POST /livestreams/{id}/join?test=1</code> (same as app, booking skipped).</p>
</div>

{{-- Live streams list --}}
<div class="test-card">
    <h2 class="text-lg font-semibold text-white mb-3">Live Streams</h2>
    <button type="button" id="refreshLive" class="test-btn-ghost mb-3">Refresh list</button>
    <div id="liveList" class="space-y-2 text-sm text-[var(--meta-text-secondary)]"></div>
</div>

{{-- Join & play --}}
<div class="test-card">
    <h2 class="text-lg font-semibold text-white mb-3">Join & play</h2>
    <p class="text-sm text-[var(--meta-text-secondary)] mb-3">Pick a stream above or enter stream ID. Set stream to Live in Admin → Livestreams → Broadcast, then start OBS with the shown RTMP URL and stream key.</p>
    <div class="flex flex-wrap gap-2 items-center mb-3">
        <input type="number" id="streamId" placeholder="Stream ID" class="test-input w-28" min="1">
        <button type="button" id="joinBtn" class="test-btn-danger">Join stream</button>
        <button type="button" id="leaveBtn" class="test-btn-ghost" disabled>Leave</button>
    </div>
    <p id="channelDisplay" class="text-xs text-[var(--meta-text-muted)] mb-1 hidden">Channel: <span id="channelName"></span></p>
    <p id="statusLine" class="text-sm text-[var(--meta-text-secondary)] mb-2">Ready.</p>
    <div id="playerWrap" class="rounded-xl overflow-hidden bg-black" style="height: 360px;">
        <div id="remoteVideo" class="w-full flex items-center justify-center text-[var(--meta-text-muted)]" style="height: 360px;">Video will appear here after join</div>
    </div>
    <details class="mt-3">
        <summary class="text-xs text-[var(--meta-text-muted)] cursor-pointer hover:text-[var(--meta-text-secondary)]">Show API response</summary>
        <pre id="joinResult" class="mt-2 p-3 rounded-lg bg-black/40 text-xs overflow-auto max-h-32 text-[var(--meta-text-secondary)]"></pre>
    </details>
</div>

<style>
    #playerWrap { height: 360px !important; }
    #remoteVideo { position: relative; width: 100%; height: 360px !important; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    #remoteVideo video { width: 100% !important; height: 100% !important; min-height: 360px; object-fit: contain; display: block; background: #000; }
    #remoteVideo > div { width: 100% !important; height: 100% !important; min-height: 360px; }
</style>

<div id="statusMessage" class="test-card hidden"></div>

<div class="flex gap-3 text-sm">
    <a href="{{ route('admin.login') }}" class="text-[var(--meta-accent-end)] hover:underline">Admin panel</a>
    <span class="text-[var(--meta-text-muted)]">|</span>
    <a href="{{ route('livestream-test.admin') }}" class="text-[var(--meta-accent-end)] hover:underline">Admin test page</a>
    <span class="text-[var(--meta-text-muted)]">|</span>
    <a href="{{ route('livestream-test.publisher') }}" class="text-[var(--meta-accent-end)] hover:underline">Publisher test</a>
</div>
@endsection

@push('scripts')
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.18.0.js"></script>
<script>
(function() {
    const API_BASE = '{{ url("/api") }}';
    const LOCAL_TEST = @json(config('services.livestream.local_test', false));
    let token = localStorage.getItem('livestream_test_user_token') || '';
    let agoraClient = null;

    const $ = (id) => document.getElementById(id);
    const setStatus = (msg, isError = false) => {
        const el = $('statusMessage');
        el.textContent = msg;
        el.className = 'test-card ' + (isError ? 'border-red-500/30 bg-red-500/10 text-red-300' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300');
        el.classList.remove('hidden');
    };
    const setStatusLine = (msg) => { const el = $('statusLine'); if (el) el.textContent = msg; };
    const showChannel = (name) => {
        const wrap = $('channelDisplay');
        const span = $('channelName');
        if (wrap && span) { span.textContent = name || ''; wrap.classList.toggle('hidden', !name); }
    };

    $('apiToken').value = token;
    $('setToken').onclick = () => {
        token = $('apiToken').value.trim();
        if (token) { localStorage.setItem('livestream_test_user_token', token); setStatus('Token saved.'); }
    };

    // Same as app: with auth use POST join?test=1; without auth (local test) use GET test-credentials
    async function getCredentials(streamId) {
        if (LOCAL_TEST) {
            const res = await fetch(API_BASE + '/livestreams/' + streamId + '/test-credentials');
            return await res.json();
        }
        if (!token) return { status_code: 401, message: 'Set API token or enable local test on server.', data: null };
        const res = await fetch(API_BASE + '/livestreams/' + streamId + '/join?test=1', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        return await res.json();
    }

    async function loadLive() {
        const url = LOCAL_TEST ? API_BASE + '/livestreams/test-live' : API_BASE + '/livestreams/live';
        const headers = { 'Accept': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        const res = await fetch(url, { headers });
        const data = await res.json();
        const list = $('liveList');
        if (!data.data || !Array.isArray(data.data) || !data.data.length) {
            list.innerHTML = '<p class="text-[var(--meta-text-muted)]">No live streams. In Admin set a stream to Live and start OBS.</p>';
            return;
        }
        list.innerHTML = data.data.map(s => `
            <div class="flex justify-between items-center py-2 border-b border-[var(--meta-border)] last:border-0">
                <span><strong class="text-white">#${s.id}</strong> ${s.title} — <span class="text-[var(--meta-text-muted)] font-mono text-xs">${s.agora_channel}</span></span>
                <button type="button" class="join-stream test-btn-danger text-sm py-1.5 px-3" data-id="${s.id}">Join</button>
            </div>
        `).join('');
        list.querySelectorAll('.join-stream').forEach(btn => {
            btn.onclick = () => { $('streamId').value = btn.dataset.id; doJoin(btn.dataset.id); };
        });
    }

    $('refreshLive').onclick = () => loadLive();

    async function doJoin(livestreamId) {
        const id = livestreamId || $('streamId').value.trim();
        if (!id) { setStatus('Enter stream ID or pick from list.', true); return; }
        if (!LOCAL_TEST && !token) { setStatus('Set API token or enable local test.', true); return; }

        setStatusLine('Getting credentials…');
        const data = await getCredentials(id);
        $('joinResult').textContent = JSON.stringify(data, null, 2);

        if (data.status_code !== 200 || !data.data || !data.data.app_id) {
            setStatusLine('Error');
            setStatus(data.message || 'Join failed', true);
            return;
        }

        const { app_id, channel, token: rtcToken } = data.data;
        const tokenToUse = (rtcToken === null || rtcToken === undefined || rtcToken === '') ? null : rtcToken;
        showChannel(channel);
        setStatusLine(tokenToUse ? 'Connecting with token…' : 'Connecting…');

        try {
            if (agoraClient) { await agoraClient.leave(); agoraClient = null; }
            agoraClient = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
            let joined = false;
            try {
                await agoraClient.join(app_id, channel, tokenToUse, null);
                joined = true;
            } catch (joinErr) {
                const jmsg = String(joinErr.message || joinErr);
                const isTokenErr = /invalid token|authorized failed/i.test(jmsg) || jmsg.includes('dynamic use static key');
                if (!joined && tokenToUse && isTokenErr) {
                    try {
                        await agoraClient.join(app_id, channel, null, null);
                        joined = true;
                    } catch (_) { throw joinErr; }
                } else if (!joined) throw joinErr;
            }
            if (!joined) throw new Error('Join failed');

            await agoraClient.setClientRole('audience');
            agoraClient.on('user-published', async (user, mediaType) => {
                await agoraClient.subscribe(user, mediaType);
                if (mediaType === 'video' && user.videoTrack) {
                    const container = $('remoteVideo');
                    if (!container) return;
                    container.innerHTML = '';
                    container.className = 'w-full';
                    container.style.height = '360px';
                    container.style.display = 'block';
                    container.style.position = 'relative';
                    await user.videoTrack.play(container, { fit: 'contain' });
                    setStatusLine('Playing.');
                    setStatus('Stream playing.');
                }
            });
            agoraClient.on('user-unpublished', () => {
                const c = $('remoteVideo');
                c.innerHTML = '<span class="text-[var(--meta-text-muted)]">Stream ended.</span>';
                c.className = 'w-full flex items-center justify-center text-[var(--meta-text-muted)]';
                c.style.height = '360px';
                c.style.display = 'flex';
            });

            $('joinBtn').disabled = true;
            $('leaveBtn').disabled = false;
            setStatusLine('Joined. Waiting for video…');
            setStatus('Joined. Waiting for host video…');
        } catch (err) {
            setStatusLine('Error');
            const msg = String(err.message || err);
            const code = err.code !== undefined ? String(err.code) : '';
            const full = code ? `[${code}] ${msg}` : msg;
            setStatus('Could not connect: ' + full, true);
            console.warn('Agora join failed:', err);
        }
    }

    $('joinBtn').onclick = () => doJoin();
    $('leaveBtn').onclick = async () => {
        if (agoraClient) { await agoraClient.leave(); agoraClient = null; }
        showChannel('');
        const rv = $('remoteVideo');
        rv.innerHTML = 'Video will appear here after join';
        rv.style.display = 'flex';
        $('joinBtn').disabled = false;
        $('leaveBtn').disabled = true;
        setStatusLine('Ready.');
        setStatus('Left stream.');
    };

    loadLive();
})();
</script>
@endpush
