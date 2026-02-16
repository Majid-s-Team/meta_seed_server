@extends('layouts.test')

@section('title', 'User Livestream Test')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">User Livestream Test</h1>
    <p class="text-gray-600">List live streams, join with Agora token (test mode skips booking). Video plays below when you join.</p>

    @if(config('services.livestream.local_test', false))
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
        <strong>Local test mode</strong> — Running locally. Join without API token. Video shows when the publisher is live on the same stream. If you get “dynamic use static key”: in <a href="https://console.agora.io" target="_blank" rel="noopener" class="underline">Agora Console</a> create a <strong>new project</strong> with <strong>「APP ID」only</strong> (not APP ID + Token), then put that App ID in <code>.env</code>. Agora has no “Testing mode” toggle — it depends on project creation.
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">API Token (Bearer)</label>
        <div class="flex gap-2">
            <input type="password" id="apiToken" placeholder="Paste token from POST /api/login" class="flex-1 border rounded px-3 py-2">
            <button type="button" id="setToken" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Set Token</button>
        </div>
        <p class="text-xs text-gray-500 mt-1">Optional when local test mode is on. Join uses <code>?test=1</code> so booking is not required.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold text-gray-800 mb-3">Live Streams</h2>
        <button type="button" id="refreshLive" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 mb-3">Refresh List</button>
        <div id="liveList" class="space-y-2 text-sm"></div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold text-gray-800 mb-3">Join &amp; Play</h2>
        <p class="text-sm text-gray-600 mb-2"><strong>Video tabhi aayegi jab publisher live ho:</strong> Pehle <a href="{{ route('livestream-test.publisher') }}" class="text-blue-600 underline">Publisher page</a> par jao → same stream ID daalo → Start Publishing. Phir yahin same stream ID se Join karo.</p>
        <p class="text-sm text-gray-600 mb-2">Click Join on a live stream above, or enter ID and click Join to connect via Agora and play video below.</p>
        <div class="flex gap-2 mb-2">
            <input type="number" id="streamId" placeholder="Stream ID" class="border rounded px-3 py-2 w-24" min="1">
            <button type="button" id="joinBtn" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Join Stream</button>
            <button type="button" id="leaveBtn" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700" disabled>Leave</button>
        </div>
        <p id="channelDisplay" class="text-xs text-gray-500 mb-2 hidden">Channel: <span id="channelName"></span></p>
        <p id="statusLine" class="text-sm text-gray-600 mb-2">Ready.</p>
        <div id="playerWrap" class="bg-black rounded overflow-hidden" style="height: 360px;">
            <div id="remoteVideo" class="remote-video-container w-full text-gray-400" style="height: 360px;">Video will appear here after Join</div>
        </div>
        <style>
            #playerWrap { height: 360px !important; }
            .remote-video-container { position: relative; width: 100%; height: 360px !important; display: block; overflow: hidden; }
            .remote-video-container video { width: 100% !important; height: 100% !important; min-height: 360px; object-fit: contain; display: block; background: #000; }
            .remote-video-container > div { width: 100% !important; height: 100% !important; min-height: 360px; }
        </style>
        <pre id="joinResult" class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-auto max-h-24 hidden"></pre>
    </div>

    <div class="flex gap-2 text-sm">
        <a href="{{ route('livestream-test.admin') }}" class="text-blue-600 hover:underline">Admin test</a>
        <span class="text-gray-400">|</span>
        <a href="{{ route('livestream-test.publisher') }}" class="text-blue-600 hover:underline">Publisher test (camera/mic)</a>
    </div>

    <div id="statusMessage" class="p-3 rounded hidden"></div>
</div>

@push('scripts')
{{-- Agora Web SDK 4.x: use official CDN or npm build --}}
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.18.0.js"></script>
<script>
(function() {
    const API_BASE = '{{ url("/api") }}';
    const LOCAL_TEST = @json(config('services.livestream.local_test', false));
    if (LOCAL_TEST) {
        console.log('LOCAL TEST MODE ACTIVE');
        console.log('LOCAL TEST MODE — skipping token validation');
    }
    let token = localStorage.getItem('livestream_test_user_token') || '';
    let agoraClient = null;

    const $ = (id) => document.getElementById(id);
    const setStatus = (msg, isError = false) => {
        const el = $('statusMessage');
        el.textContent = msg;
        el.className = 'p-3 rounded ' + (isError ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800');
        el.classList.remove('hidden');
    };
    const setStatusLine = (msg) => {
        const el = $('statusLine');
        if (el) el.textContent = msg;
    };
    const showChannel = (name) => {
        const wrap = $('channelDisplay');
        const span = $('channelName');
        if (wrap && span) { span.textContent = name || ''; wrap.classList.toggle('hidden', !name); }
    };

    $('apiToken').value = token;
    $('setToken').onclick = () => {
        token = $('apiToken').value.trim();
        if (token) {
            localStorage.setItem('livestream_test_user_token', token);
            setStatus('Token saved.');
        }
    };

    const api = (path, options = {}) => {
        const url = API_BASE + path;
        const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', ...options.headers };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        return fetch(url, { ...options, headers });
    };

    async function getCredentials(streamId) {
        if (LOCAL_TEST) {
            const res = await fetch(API_BASE + '/livestreams/' + streamId + '/test-credentials');
            return await res.json();
        }
        if (!token) {
            return { status_code: 401, message: 'Set API token first (or enable local test mode).', data: null };
        }
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
        if (!data.data || !data.data.length) {
            list.innerHTML = '<p class="text-gray-500">No live streams. Use Admin page to set a stream LIVE.</p>';
            return;
        }
        list.innerHTML = data.data.map(s => `
            <div class="border rounded p-2 flex justify-between items-center">
                <span><strong>#${s.id}</strong> ${s.title} — ${s.agora_channel}</span>
                <button type="button" class="join-stream bg-red-600 text-white px-3 py-1 rounded text-xs" data-id="${s.id}">Join</button>
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
        if (!LOCAL_TEST && !token) { setStatus('Set API token first (or enable local test mode).', true); return; }

        setStatusLine('Getting credentials...');
        const data = await getCredentials(id);
        $('joinResult').textContent = JSON.stringify(data, null, 2);
        $('joinResult').classList.remove('hidden');

        if (data.status_code !== 200 || !data.data || !data.data.app_id) {
            setStatusLine('Error');
            setStatus(data.message || 'Join failed', true);
            return;
        }

        const { app_id, channel, token: rtcToken } = data.data;
        // In local test mode: always use null token so Agora auth never blocks
        const tokenToUse = LOCAL_TEST ? null : ((rtcToken === null || rtcToken === undefined || rtcToken === '') ? null : rtcToken);
        if (LOCAL_TEST) {
            console.log('LOCAL TEST MODE — skipping token validation');
        }
        showChannel(channel);
        setStatusLine('Connecting to Agora...');
        setStatus(LOCAL_TEST ? 'Connecting (local test, no token)...' : 'Connecting...');

        try {
            if (agoraClient) {
                await agoraClient.leave();
                agoraClient = null;
            }

            agoraClient = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
            let joined = false;
            try {
                await agoraClient.join(app_id, channel, tokenToUse, null);
                joined = true;
            } catch (joinErr) {
                if (LOCAL_TEST && (String(joinErr.message || joinErr).toLowerCase().indexOf('invalid token') !== -1 || String(joinErr.message || joinErr).indexOf('authorized failed') !== -1)) {
                    console.warn('[Viewer] Join failed with token error in local test — retrying without token');
                    await agoraClient.join(app_id, channel, null, null);
                    joined = true;
                } else {
                    throw joinErr;
                }
            }

            if (!joined) throw new Error('Join failed');

            await agoraClient.setClientRole('audience');
            console.log('[Viewer] Role set to audience');

            agoraClient.on('user-published', async (user, mediaType) => {
                console.log('[Viewer] user-published', mediaType, user.uid);
                await agoraClient.subscribe(user, mediaType);
                if (mediaType === 'video' && user.videoTrack) {
                    const container = document.getElementById('remoteVideo');
                    if (!container) { console.warn('[Viewer] remoteVideo container not found'); return; }
                    container.innerHTML = '';
                    container.className = 'remote-video-container w-full';
                    container.style.height = '360px';
                    container.style.display = 'block';
                    await user.videoTrack.play(container, { fit: 'contain' });
                    console.log('[Viewer] Remote video playing');
                    setStatusLine(LOCAL_TEST ? 'Playing stream (local test).' : 'Playing stream.');
                    setStatus(LOCAL_TEST ? 'Playing stream (local test).' : 'Playing stream.');
                }
            });
            agoraClient.on('user-unpublished', () => {
                const container = $('remoteVideo');
                container.innerHTML = '<span class="text-gray-400">Stream ended or left.</span>';
                container.className = 'remote-video-container w-full';
                container.style.height = '360px';
            });

            $('joinBtn').disabled = true;
            $('leaveBtn').disabled = false;
            setStatusLine(LOCAL_TEST ? 'Joined (local test). Waiting for host video...' : 'Joined. Waiting for host video...');
            setStatus(LOCAL_TEST ? 'LOCAL TEST MODE ACTIVE. Joined without token. Waiting for host video...' : 'Joined. Waiting for host video...');
        } catch (err) {
            setStatusLine('Error');
            const msg = String(err.message || err);
            const isTokenError = msg.toLowerCase().indexOf('invalid token') !== -1 || msg.indexOf('authorized failed') !== -1;
            const isGatewayError = msg.indexOf('CAN_NOT_GET_GATEWAY_SERVER') !== -1 || msg.indexOf('dynamic use static key') !== -1;
            if (LOCAL_TEST && (isTokenError || isGatewayError)) {
                setStatus('LOCAL TEST MODE ACTIVE. Could not connect to Agora. In Agora Console set project to Testing mode (no token required) and check network.', false);
                setStatusLine('Local test — Agora unreachable');
                console.warn('[Viewer] Agora join failed in local test:', err);
            } else if (LOCAL_TEST) {
                setStatus('LOCAL TEST MODE ACTIVE. Join failed. Ensure publisher is on the same channel.', false);
                setStatusLine('Local test — join failed');
                console.warn('[Viewer] Agora join failed in local test:', err);
            } else {
                const friendly = isTokenError ? 'Invalid token. Check AGORA_APP_ID and AGORA_APP_CERTIFICATE in .env.' : (isGatewayError ? 'Cannot reach Agora (check network; set project to Testing mode in Console).' : msg);
                setStatus('Agora error: ' + friendly, true);
                console.error(err);
            }
        }
    }

    $('joinBtn').onclick = () => doJoin();

    $('leaveBtn').onclick = async () => {
        if (agoraClient) {
            await agoraClient.leave();
            agoraClient = null;
        }
        showChannel('');
        const rv = $('remoteVideo');
        rv.innerHTML = 'Video will appear here after Join';
        rv.className = 'remote-video-container w-full';
        rv.style.height = '360px';
        $('joinBtn').disabled = false;
        $('leaveBtn').disabled = true;
        setStatusLine('Ready.');
        setStatus('Left stream.');
    };

    loadLive();
})();
</script>
@endpush
@endsection
