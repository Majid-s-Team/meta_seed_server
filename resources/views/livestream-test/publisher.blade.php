@extends('layouts.test')

@section('title', 'Livestream Publisher Test')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Livestream Publisher Test</h1>
    <p class="text-gray-600">Publish your camera and microphone to an Agora channel. Use a <strong>LIVE</strong> stream ID from the admin page so viewers can watch on the user test page.</p>

    @if(config('services.livestream.local_test', false))
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
        <strong>Local test mode</strong> — Running locally. Camera preview works without Agora. To stream to the viewer: in <a href="https://console.agora.io" target="_blank" rel="noopener" class="underline">Agora Console</a> create a <strong>new project</strong> and choose <strong>「APP ID」only</strong> (not APP ID + Token) as authentication — then use that App ID in <code>.env</code>. There is no “Testing mode” toggle; it depends on how the project was created.
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">API Token (Bearer)</label>
        <div class="flex gap-2">
            <input type="password" id="apiToken" placeholder="Paste token from POST /api/login" class="flex-1 border rounded px-3 py-2">
            <button type="button" id="setToken" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Set Token</button>
        </div>
        <p class="text-xs text-gray-500 mt-1">Required when local test mode is off. Stream must be set to LIVE first (admin page).</p>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold text-gray-800 mb-3">Start Publishing</h2>
        <p class="text-sm text-gray-600 mb-3">Enter a <strong>live</strong> stream ID (from admin: create stream → Go LIVE → use that ID). You will join as host and publish camera + mic.</p>
        <div class="flex flex-wrap gap-2 items-center mb-2">
            <input type="number" id="streamId" placeholder="Live stream ID" class="border rounded px-3 py-2 w-32" min="1">
            <button type="button" id="startPublish" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Start Publishing</button>
            <button type="button" id="stopPublish" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" disabled>Stop Publishing</button>
        </div>
        <p id="channelDisplay" class="text-xs text-gray-500 mb-2 hidden">Channel: <span id="channelName"></span></p>
        <p id="statusLine" class="text-sm text-gray-600 mb-2">Ready.</p>
        <div id="localPreviewWrap" class="bg-black rounded overflow-hidden max-w-lg" style="height: 280px;">
            <div id="localVideo" class="local-preview-container w-full h-full text-gray-400" style="height: 280px;">Local preview will appear here</div>
        </div>
        <style>
            #localPreviewWrap { height: 280px !important; }
            .local-preview-container { position: relative; width: 100%; height: 280px !important; display: block; overflow: hidden; }
            .local-preview-container video { width: 100% !important; height: 100% !important; min-height: 280px; object-fit: contain; display: block; background: #000; }
            .local-preview-container > div { width: 100% !important; height: 100% !important; min-height: 280px; }
        </style>
        <pre id="publishResult" class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-auto max-h-24 hidden"></pre>
    </div>

    <div class="flex gap-2 text-sm">
        <a href="{{ route('livestream-test.admin') }}" class="text-blue-600 hover:underline">Admin test</a>
        <span class="text-gray-400">|</span>
        <a href="{{ route('livestream-test.user') }}" class="text-blue-600 hover:underline">User test (watch)</a>
    </div>

    <div id="statusMessage" class="p-3 rounded hidden"></div>
</div>

@push('scripts')
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.18.0.js"></script>
<script>
(function() {
    const API_BASE = '{{ url("/api") }}';
    const LOCAL_TEST = @json(config('services.livestream.local_test', false));
    if (LOCAL_TEST) {
        console.log('LOCAL TEST MODE ACTIVE');
        console.log('Skipping Agora authorization');
    }
    let token = localStorage.getItem('livestream_test_publisher_token') || '';
    let agoraClient = null;
    let localTracks = { video: null, audio: null };

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
            localStorage.setItem('livestream_test_publisher_token', token);
            setStatus('Token saved.');
        }
    };

    async function stopPublishing() {
        try {
            if (localTracks.video) { localTracks.video.close(); localTracks.video = null; }
            if (localTracks.audio) { localTracks.audio.close(); localTracks.audio = null; }
            if (agoraClient) {
                await agoraClient.leave();
                agoraClient = null;
            }
            const container = $('localVideo');
            container.innerHTML = 'Local preview will appear here';
            container.id = 'localVideo';
            $('startPublish').disabled = false;
            $('stopPublish').disabled = true;
            setStatusLine('Stopped.');
            setStatus('Stopped publishing.');
        } catch (e) {
            console.error('Publisher stop error:', e);
            setStatus('Error stopping: ' + (e.message || e), true);
        }
    }

    $('stopPublish').onclick = stopPublishing;

    async function getCredentials(streamId) {
        if (LOCAL_TEST) {
            console.log('[Publisher] Getting credentials (local test, no auth)');
            const res = await fetch(API_BASE + '/livestreams/' + streamId + '/test-credentials');
            const data = await res.json();
            return data;
        }
        if (!token) {
            return { status_code: 401, message: 'Set API token first.', data: null };
        }
        console.log('[Publisher] Getting credentials (join API with token)');
        const res = await fetch(API_BASE + '/livestreams/' + streamId + '/join?test=1', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        return await res.json();
    }

    $('startPublish').onclick = async () => {
        const id = $('streamId').value.trim();
        if (!id) { setStatus('Enter a live stream ID.', true); return; }
        if (!LOCAL_TEST && !token) { setStatus('Set API token first (or enable local test mode).', true); return; }

        setStatusLine('Getting credentials...');
        setStatus('Getting credentials...');
        let data;
        try {
            data = await getCredentials(id);
        } catch (e) {
            console.error('Join API error:', e);
            setStatusLine('Error');
            setStatus('Network error getting credentials.', true);
            return;
        }

        $('publishResult').textContent = JSON.stringify(data, null, 2);
        $('publishResult').classList.remove('hidden');

        if (data.status_code !== 200 || !data.data || !data.data.app_id) {
            console.error('Join API failed:', data);
            setStatusLine('Error');
            setStatus(data.message || 'Failed to get credentials. Is the stream LIVE?', true);
            return;
        }

        const { app_id, channel, token: rtcToken } = data.data;
        // In local test mode: always use null token so Agora auth never blocks; skip authorization
        const tokenToUse = LOCAL_TEST ? null : ((rtcToken === null || rtcToken === undefined || rtcToken === '') ? null : rtcToken);
        if (LOCAL_TEST) {
            console.log('LOCAL TEST MODE ACTIVE');
            console.log('Skipping Agora authorization');
        }
        console.log('[Publisher] Joining channel:', channel, 'token:', tokenToUse ? 'present' : 'null');

        showChannel(channel);
        setStatusLine('Requesting camera and microphone...');
        setStatus('Allow camera and microphone when prompted...');

        try {
            if (agoraClient) await stopPublishing();

            let audioTrack, videoTrack;
            try {
                [audioTrack, videoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
                localTracks.audio = audioTrack;
                localTracks.video = videoTrack;
                console.log('camera track created');
                console.log('[Publisher] Permission granted, tracks created');
            } catch (trackErr) {
                console.error('Track creation failed:', trackErr);
                setStatus('Camera/mic failed: ' + (trackErr.message || trackErr), true);
                setStatusLine('Error');
                return;
            }

            const container = document.getElementById('localVideo');
            if (!container) {
                console.error('localVideo element not found');
                setStatus('Preview container #localVideo not found.', true);
                return;
            }
            container.innerHTML = '';
            container.className = 'local-preview-container w-full';
            container.style.height = '280px';
            container.style.display = 'block';

            try {
                console.log('playing local preview');
                await videoTrack.play(container, { fit: 'contain', mirror: true });
                console.log('local preview playing');
            } catch (playErr) {
                console.error('Local preview play failed:', playErr);
                setStatus('Preview failed: ' + (playErr.message || playErr), true);
                setStatusLine('Error');
                if (localTracks.video) { localTracks.video.close(); localTracks.video = null; }
                if (localTracks.audio) { localTracks.audio.close(); localTracks.audio = null; }
                return;
            }
            setStatusLine('Camera ready');
            setStatus('Local preview active');

            agoraClient = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
            try {
                await agoraClient.join(app_id, channel, tokenToUse, null);
                console.log('[Publisher] Joined channel');
            } catch (joinErr) {
                if (LOCAL_TEST) {
                    const jmsg = String(joinErr.message || joinErr);
                    const isGateway = jmsg.indexOf('CAN_NOT_GET_GATEWAY_SERVER') !== -1 || jmsg.indexOf('dynamic use static key') !== -1;
                    console.warn('[Publisher] Agora join failed (local test - continuing with preview only):', joinErr);
                    setStatusLine('Local preview active (local test)');
                    setStatus(isGateway ? 'Camera ready. Local preview active. Agora unreachable — set project to Testing mode in Agora Console.' : 'Camera ready. Local preview active. Agora join skipped in local test mode.');
                    $('startPublish').disabled = true;
                    $('stopPublish').disabled = false;
                    return;
                }
                throw joinErr;
            }

            await agoraClient.setClientRole('host');
            console.log('[Publisher] Role set to host');

            setStatusLine('Publishing...');
            try {
                await agoraClient.publish([localTracks.audio, localTracks.video]);
                console.log('[Publisher] Publishing live');
            } catch (publishErr) {
                if (LOCAL_TEST) {
                    console.warn('[Publisher] Agora publish failed (local test - keeping preview):', publishErr);
                    setStatusLine('Local preview active (local test)');
                    setStatus('Camera ready. Local preview active. Publish failed in local test mode.');
                    $('startPublish').disabled = true;
                    $('stopPublish').disabled = false;
                    return;
                }
                throw publishErr;
            }

            $('startPublish').disabled = true;
            $('stopPublish').disabled = false;
            setStatusLine(LOCAL_TEST ? 'Publishing (local test)' : 'Publishing live');
            setStatus(LOCAL_TEST ? 'Publishing (local test). Camera ready, local preview active.' : 'Publishing. Viewers can join on the User test page.');
        } catch (err) {
            console.error('Publisher error:', err);
            setStatusLine('Error');
            const msg = err.message || String(err);
            const friendly = msg.indexOf('invalid token') !== -1 || msg.indexOf('authorized failed') !== -1
                ? 'Invalid token. Check AGORA_APP_ID and AGORA_APP_CERTIFICATE in .env (no spaces after =).'
                : msg;
            setStatus('Error: ' + friendly, true);
            $('startPublish').disabled = false;
            if (!LOCAL_TEST) {
                await stopPublishing();
            }
        }
    };
})();
</script>
@endpush
@endsection
