@extends('layouts.test')

@section('title', 'Admin Livestream Test')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Admin Livestream Test</h1>
    <p class="text-gray-600">Create streams, set LIVE, end stream. Use an <strong>admin</strong> API token.</p>

    <div class="bg-white rounded-lg shadow p-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">API Token (Bearer)</label>
        <div class="flex gap-2">
            <input type="password" id="apiToken" placeholder="Paste token from POST /api/login" class="flex-1 border rounded px-3 py-2">
            <button type="button" id="setToken" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Set Token</button>
        </div>
        <p class="text-xs text-gray-500 mt-1">Get token: POST /api/login with email & password (admin user).</p>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold text-gray-800 mb-3">Create Stream</h2>
        <div class="grid gap-2 text-sm">
            <input type="text" id="createTitle" placeholder="Title" class="border rounded px-3 py-2" value="Test Stream">
            <input type="text" id="createChannel" placeholder="Agora channel name" class="border rounded px-3 py-2" value="test-channel-{{ time() }}">
            <input type="datetime-local" id="createScheduled" class="border rounded px-3 py-2">
            <button type="button" id="createStream" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-fit">Create Stream</button>
        </div>
        <pre id="createResult" class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-auto max-h-32 hidden"></pre>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold text-gray-800 mb-3">Streams (all statuses)</h2>
        <button type="button" id="refreshStreams" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 mb-3">Refresh List</button>
        <div id="streamList" class="space-y-2 text-sm"></div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold text-gray-800 mb-3">Actions</h2>
        <div class="flex flex-wrap gap-2">
            <input type="number" id="streamId" placeholder="Stream ID" class="border rounded px-3 py-2 w-24" min="1">
            <button type="button" id="goLive" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Go LIVE</button>
            <button type="button" id="endStream" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">End Stream</button>
        </div>
        <pre id="actionResult" class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-auto max-h-32 hidden"></pre>
    </div>

    <div class="flex gap-2 text-sm">
        <a href="{{ route('livestream-test.publisher') }}" class="text-blue-600 hover:underline">Publisher test (camera/mic)</a>
        <span class="text-gray-400">|</span>
        <a href="{{ route('livestream-test.user') }}" class="text-blue-600 hover:underline">User test (watch)</a>
    </div>

    <div id="statusMessage" class="p-3 rounded hidden"></div>
</div>

@push('scripts')
<script>
(function() {
    const API_BASE = '{{ url("/api") }}';
    let token = localStorage.getItem('livestream_test_admin_token') || '';

    const $ = (id) => document.getElementById(id);
    const setStatus = (msg, isError = false) => {
        const el = $('statusMessage');
        el.textContent = msg;
        el.className = 'p-3 rounded ' + (isError ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800');
        el.classList.remove('hidden');
    };

    $('apiToken').value = token;
    $('setToken').onclick = () => {
        token = $('apiToken').value.trim();
        if (token) {
            localStorage.setItem('livestream_test_admin_token', token);
            setStatus('Token saved.');
        }
    };

    const api = (path, options = {}) => {
        const url = API_BASE + path;
        const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', ...options.headers };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        return fetch(url, { ...options, headers });
    };

    const setCreateDate = () => {
        const d = new Date();
        d.setMinutes(d.getMinutes() + 10);
        $('createScheduled').value = d.toISOString().slice(0, 16);
    };
    setCreateDate();

    $('createStream').onclick = async () => {
        const title = $('createTitle').value.trim();
        const agora_channel = $('createChannel').value.trim();
        const scheduled_at = $('createScheduled').value ? new Date($('createScheduled').value).toISOString().slice(0, 19).replace('T', ' ') : null;
        if (!title || !agora_channel) { setStatus('Title and channel required.', true); return; }
        const res = await api('/admin/livestreams', {
            method: 'POST',
            body: JSON.stringify({ title, description: 'Test', scheduled_at: scheduled_at || null, agora_channel, price: 0, max_participants: 100 })
        });
        const data = await res.json();
        const pre = $('createResult');
        pre.textContent = JSON.stringify(data, null, 2);
        pre.classList.remove('hidden');
        if (data.data && data.data.id) {
            $('streamId').value = data.data.id;
            setStatus('Stream created. ID: ' + data.data.id);
            loadStreams();
        } else setStatus(data.message || 'Create failed', true);
    };

    async function loadStreams() {
        const res = await api('/admin/livestreams');
        const data = await res.json();
        const list = $('streamList');
        if (!data.data || !data.data.length) { list.innerHTML = '<p class="text-gray-500">No streams.</p>'; return; }
        list.innerHTML = data.data.map(s => `
            <div class="border rounded p-2 flex justify-between items-center">
                <span><strong>#${s.id}</strong> ${s.title} — <span class="font-medium">${s.status}</span> (${s.agora_channel})</span>
                <button type="button" class="use-id text-blue-600 text-xs" data-id="${s.id}">Use ID</button>
            </div>
        `).join('');
        list.querySelectorAll('.use-id').forEach(btn => btn.onclick = () => { $('streamId').value = btn.dataset.id; });
    }

    $('refreshStreams').onclick = () => loadStreams();

    $('goLive').onclick = async () => {
        const id = $('streamId').value.trim();
        if (!id) { setStatus('Enter stream ID.', true); return; }
        const res = await api('/admin/livestreams/' + id + '/go-live', { method: 'POST' });
        const data = await res.json();
        $('actionResult').textContent = JSON.stringify(data, null, 2);
        $('actionResult').classList.remove('hidden');
        setStatus(data.data ? 'Stream is LIVE.' : (data.message || 'Failed'), !data.data);
        loadStreams();
    };

    $('endStream').onclick = async () => {
        const id = $('streamId').value.trim();
        if (!id) { setStatus('Enter stream ID.', true); return; }
        const res = await api('/admin/livestreams/' + id + '/end-stream', { method: 'POST' });
        const data = await res.json();
        $('actionResult').textContent = JSON.stringify(data, null, 2);
        $('actionResult').classList.remove('hidden');
        setStatus(data.data ? 'Stream ended.' : (data.message || 'Failed'), !data.data);
        loadStreams();
    };

    loadStreams();
})();
</script>
@endpush
@endsection
