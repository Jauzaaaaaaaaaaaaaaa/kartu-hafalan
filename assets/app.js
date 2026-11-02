let activeUtterance = null;
let activeTtsPlayer = null;
let activeSegments = [];
let activeSegmentIndex = 0;
let isSpeechPaused = false;
let youtubePlayers = [];
let youtubeApiRequested = false;
let youtubeApiReady = false;
let youtubePlayerCounter = 0;

function formatTime(seconds) {
  if (!isFinite(seconds) || seconds < 0) return '00:00';
  const min = Math.floor(seconds / 60);
  const sec = Math.floor(seconds % 60);
  return String(min).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
}

function normalizeArabicText(text) {
  return (text || '').replace(/\s+/g, ' ').trim();
}

function splitArabicSegments(text) {
  const normalized = normalizeArabicText(text);
  if (!normalized) return [];
  const parts = normalized
    .split(/([،,.؛;؟?]|\u06DD|﴿[^﴾]*﴾)/)
    .reduce((acc, item) => {
      if (!item || !item.trim()) return acc;
      const last = acc[acc.length - 1] || '';
      if (/^[،,.؛;؟?]|^\u06DD|^﴿/.test(item.trim()) && acc.length) {
        acc[acc.length - 1] = (last + ' ' + item.trim()).trim();
      } else {
        acc.push(item.trim());
      }
      return acc;
    }, []);

  const segments = [];
  let buffer = '';
  parts.forEach(part => {
    const candidate = buffer ? buffer + ' ' + part : part;
    if (candidate.length > 95 && buffer) {
      segments.push(buffer.trim());
      buffer = part;
    } else {
      buffer = candidate;
    }
  });
  if (buffer.trim()) segments.push(buffer.trim());
  return segments.length ? segments : [normalized];
}

function stopSpeaking() {
  if ('speechSynthesis' in window) window.speechSynthesis.cancel();
  activeUtterance = null;
  activeTtsPlayer = null;
  activeSegments = [];
  activeSegmentIndex = 0;
  isSpeechPaused = false;
  document.body.classList.remove('speaking');
}

function pauseAllMedia(exceptYoutubePlayer = null) {
  document.querySelectorAll('audio').forEach(audio => audio.pause());
  stopSpeaking();
  youtubePlayers.forEach(item => {
    if (item && item.player && item.player !== exceptYoutubePlayer && typeof item.player.pauseVideo === 'function') {
      try { item.player.pauseVideo(); } catch (err) {}
    }
  });
}

function updateTtsLabels(player, index, total) {
  const current = player.querySelector('[data-tts-current]');
  const totalEl = player.querySelector('[data-tts-total]');
  const range = player.querySelector('[data-tts-range]');
  const safeTotal = Math.max(total, 1);
  const safeIndex = Math.max(0, Math.min(index, safeTotal - 1));
  if (current) current.textContent = 'Bagian ' + (safeIndex + 1);
  if (totalEl) totalEl.textContent = 'Bagian ' + safeTotal;
  if (range) {
    range.max = String(safeTotal - 1);
    range.value = String(safeIndex);
  }
}

function speakSegmentFrom(player, startIndex = 0) {
  if (!('speechSynthesis' in window)) {
    alert('Browser ini belum mendukung suara Arab bawaan. Coba gunakan Chrome atau Edge terbaru.');
    return;
  }
  const text = player.getAttribute('data-tts-text') || '';
  const segments = splitArabicSegments(text);
  if (!segments.length) return;

  pauseAllMedia();
  activeTtsPlayer = player;
  activeSegments = segments;
  activeSegmentIndex = Math.max(0, Math.min(Number(startIndex || 0), segments.length - 1));
  document.body.classList.add('speaking');

  function speakNext() {
    if (!activeTtsPlayer || activeTtsPlayer !== player) return;
    if (activeSegmentIndex >= activeSegments.length) {
      stopSpeaking();
      return;
    }
    updateTtsLabels(player, activeSegmentIndex, activeSegments.length);
    const utter = new SpeechSynthesisUtterance(activeSegments[activeSegmentIndex]);
    utter.lang = 'ar-SA';
    utter.rate = 0.78;
    utter.pitch = 1;
    utter.onend = () => {
      if (!activeTtsPlayer || activeTtsPlayer !== player) return;
      activeSegmentIndex += 1;
      speakNext();
    };
    utter.onerror = () => stopSpeaking();
    activeUtterance = utter;
    window.speechSynthesis.speak(utter);
  }
  speakNext();
}

function pauseSpeaking() {
  if ('speechSynthesis' in window && window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
    window.speechSynthesis.pause();
    isSpeechPaused = true;
  }
}

function resumeSpeaking() {
  if ('speechSynthesis' in window && window.speechSynthesis.paused) {
    window.speechSynthesis.resume();
    isSpeechPaused = false;
  }
}

function setupTtsPlayer(player) {
  const segments = splitArabicSegments(player.getAttribute('data-tts-text') || '');
  const range = player.querySelector('[data-tts-range]');
  updateTtsLabels(player, 0, segments.length || 1);

  player.querySelector('[data-tts-play]')?.addEventListener('click', () => {
    const start = range ? Number(range.value || 0) : 0;
    speakSegmentFrom(player, start);
  });
  player.querySelector('[data-tts-pause]')?.addEventListener('click', pauseSpeaking);
  player.querySelector('[data-tts-resume]')?.addEventListener('click', resumeSpeaking);
  player.querySelector('[data-tts-stop]')?.addEventListener('click', () => {
    stopSpeaking();
    updateTtsLabels(player, range ? Number(range.value || 0) : 0, segments.length || 1);
  });
  player.querySelector('[data-tts-back]')?.addEventListener('click', () => {
    const current = range ? Number(range.value || 0) : activeSegmentIndex;
    const next = Math.max(0, current - 1);
    updateTtsLabels(player, next, segments.length || 1);
    if (activeTtsPlayer === player && document.body.classList.contains('speaking')) speakSegmentFrom(player, next);
  });
  player.querySelector('[data-tts-forward]')?.addEventListener('click', () => {
    const current = range ? Number(range.value || 0) : activeSegmentIndex;
    const next = Math.min((segments.length || 1) - 1, current + 1);
    updateTtsLabels(player, next, segments.length || 1);
    if (activeTtsPlayer === player && document.body.classList.contains('speaking')) speakSegmentFrom(player, next);
  });
  range?.addEventListener('input', () => {
    const next = Number(range.value || 0);
    updateTtsLabels(player, next, segments.length || 1);
    if (activeTtsPlayer === player && document.body.classList.contains('speaking')) speakSegmentFrom(player, next);
  });
}

function setupAudioPlayer(player) {
  const audio = player.querySelector('audio');
  if (!audio) return;
  let playlist = [];
  let trackIndex = 0;
  try { playlist = JSON.parse(player.getAttribute('data-playlist') || '[]'); } catch (err) { playlist = []; }

  const playBtn = player.querySelector('[data-audio-play]');
  const pauseBtn = player.querySelector('[data-audio-pause]');
  const stopBtn = player.querySelector('[data-audio-stop]');
  const backBtn = player.querySelector('[data-audio-back]');
  const forwardBtn = player.querySelector('[data-audio-forward]');
  const prevBtn = player.querySelector('[data-audio-prev]');
  const nextBtn = player.querySelector('[data-audio-next]');
  const range = player.querySelector('[data-audio-range]');
  const current = player.querySelector('[data-audio-current]');
  const duration = player.querySelector('[data-audio-duration]');
  const trackCurrent = player.querySelector('[data-track-current]');
  const trackTotal = player.querySelector('[data-track-total]');

  function updateTrackLabels() {
    if (trackCurrent) trackCurrent.textContent = String(trackIndex + 1);
    if (trackTotal) trackTotal.textContent = String(playlist.length || 1);
  }

  function loadTrack(index, autoplay = false) {
    if (!playlist.length) return;
    trackIndex = Math.max(0, Math.min(index, playlist.length - 1));
    audio.src = playlist[trackIndex];
    audio.currentTime = 0;
    updateTrackLabels();
    if (autoplay) audio.play().catch(() => alert('Audio belum bisa diputar. Pastikan internet aktif atau sumber audio bisa diakses.'));
  }

  function refresh() {
    if (range && !range.matches(':active')) range.value = audio.currentTime || 0;
    if (current) current.textContent = formatTime(audio.currentTime || 0);
    if (duration) duration.textContent = formatTime(audio.duration || 0);
  }
  function setDuration() {
    if (range) range.max = isFinite(audio.duration) ? audio.duration : 0;
    refresh();
  }

  playBtn && playBtn.addEventListener('click', () => {
    pauseAllMedia();
    audio.play().catch(() => alert('Audio belum bisa diputar. Pastikan internet aktif atau sumber audio bisa diakses.'));
  });
  pauseBtn && pauseBtn.addEventListener('click', () => audio.pause());
  stopBtn && stopBtn.addEventListener('click', () => { audio.pause(); if (playlist.length) loadTrack(0, false); audio.currentTime = 0; refresh(); });
  backBtn && backBtn.addEventListener('click', () => { audio.currentTime = Math.max(0, (audio.currentTime || 0) - 10); refresh(); });
  forwardBtn && forwardBtn.addEventListener('click', () => { audio.currentTime = Math.min(audio.duration || 0, (audio.currentTime || 0) + 10); refresh(); });
  prevBtn && prevBtn.addEventListener('click', () => loadTrack(trackIndex - 1, true));
  nextBtn && nextBtn.addEventListener('click', () => loadTrack(trackIndex + 1, true));
  range && range.addEventListener('input', () => { audio.currentTime = Number(range.value || 0); refresh(); });
  audio.addEventListener('ended', () => { if (playlist.length && trackIndex < playlist.length - 1) loadTrack(trackIndex + 1, true); });
  audio.addEventListener('loadedmetadata', setDuration);
  audio.addEventListener('timeupdate', refresh);
  audio.addEventListener('durationchange', setDuration);
  updateTrackLabels();
  setDuration();
}

function loadYouTubeApi() {
  if (youtubeApiRequested) return;
  youtubeApiRequested = true;
  const script = document.createElement('script');
  script.src = 'https://www.youtube.com/iframe_api';
  script.async = true;
  document.head.appendChild(script);
}

window.onYouTubeIframeAPIReady = function () {
  youtubeApiReady = true;
  setupAllYouTubePlayers();
};

function setupAllYouTubePlayers() {
  if (!youtubeApiReady || !window.YT || !window.YT.Player) return;
  document.querySelectorAll('.youtube-player').forEach(setupYouTubePlayer);
}

function setupYouTubePlayer(panel) {
  if (panel.dataset.ytReady === '1') return;
  const videoId = panel.getAttribute('data-youtube-id');
  if (!videoId) return;
  panel.dataset.ytReady = '1';

  const frame = panel.querySelector('[data-youtube-frame]');
  const range = panel.querySelector('[data-youtube-range]');
  const current = panel.querySelector('[data-youtube-current]');
  const duration = panel.querySelector('[data-youtube-duration]');
  const playBtn = panel.querySelector('[data-youtube-play]');
  const pauseBtn = panel.querySelector('[data-youtube-pause]');
  const stopBtn = panel.querySelector('[data-youtube-stop]');
  const backBtn = panel.querySelector('[data-youtube-back]');
  const forwardBtn = panel.querySelector('[data-youtube-forward]');
  const status = panel.querySelector('[data-youtube-status]');
  const frameId = 'youtube-frame-' + (++youtubePlayerCounter);
  frame.id = frameId;

  let timer = null;
  let player = null;

  function safeDuration() {
    try { return player && player.getDuration ? player.getDuration() || 0 : 0; } catch (err) { return 0; }
  }
  function safeCurrent() {
    try { return player && player.getCurrentTime ? player.getCurrentTime() || 0 : 0; } catch (err) { return 0; }
  }
  function refresh() {
    const d = safeDuration();
    const c = safeCurrent();
    if (range && !range.matches(':active')) range.value = String(c);
    if (range) range.max = String(d || 0);
    if (current) current.textContent = formatTime(c);
    if (duration) duration.textContent = formatTime(d);
  }
  function startTimer() {
    stopTimer();
    timer = setInterval(refresh, 300);
  }
  function stopTimer() {
    if (timer) clearInterval(timer);
    timer = null;
  }

  player = new YT.Player(frameId, {
    width: '100%',
    height: '280',
    videoId,
    playerVars: {
      controls: 0,
      rel: 0,
      modestbranding: 1,
      playsinline: 1,
      origin: window.location.origin
    },
    events: {
      onReady: () => {
        refresh();
        if (status) status.textContent = 'Siap diputar';
      },
      onStateChange: (event) => {
        if (event.data === YT.PlayerState.PLAYING) {
          startTimer();
          if (status) status.textContent = 'Memutar';
        } else if (event.data === YT.PlayerState.PAUSED) {
          refresh();
          stopTimer();
          if (status) status.textContent = 'Pause';
        } else if (event.data === YT.PlayerState.ENDED) {
          refresh();
          stopTimer();
          if (status) status.textContent = 'Selesai';
        }
      },
      onError: () => {
        if (status) status.textContent = 'Video YouTube tidak bisa diputar. Coba buka link sumber.';
      }
    }
  });

  youtubePlayers.push({ panel, player, stopTimer });

  playBtn?.addEventListener('click', () => {
    pauseAllMedia(player);
    try { player.playVideo(); startTimer(); } catch (err) { alert('Player YouTube belum siap. Tunggu sebentar lalu klik Play lagi.'); }
  });
  pauseBtn?.addEventListener('click', () => { try { player.pauseVideo(); } catch (err) {} });
  stopBtn?.addEventListener('click', () => {
    try { player.pauseVideo(); player.seekTo(0, true); refresh(); stopTimer(); } catch (err) {}
  });
  backBtn?.addEventListener('click', () => {
    try { player.seekTo(Math.max(0, safeCurrent() - 10), true); refresh(); } catch (err) {}
  });
  forwardBtn?.addEventListener('click', () => {
    try { player.seekTo(Math.min(safeDuration(), safeCurrent() + 10), true); refresh(); } catch (err) {}
  });
  range?.addEventListener('input', () => {
    try { player.seekTo(Number(range.value || 0), true); refresh(); } catch (err) {}
  });
}

document.addEventListener('DOMContentLoaded', function () {
  const note = document.querySelector('.practice-box textarea');
  if (note) {
    const key = 'note:' + location.pathname + location.search;
    note.value = localStorage.getItem(key) || '';
    note.addEventListener('input', () => localStorage.setItem(key, note.value));
  }

  document.querySelectorAll('.custom-audio-player').forEach(setupAudioPlayer);
  document.querySelectorAll('.tts-player').forEach(setupTtsPlayer);
  if (document.querySelector('.youtube-player')) loadYouTubeApi();
  window.addEventListener('beforeunload', () => {
    stopSpeaking();
    youtubePlayers.forEach(item => item.stopTimer && item.stopTimer());
  });
});
