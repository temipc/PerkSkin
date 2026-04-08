(function () {
  const dataEl = document.getElementById('events-page-data');
  const grid = document.getElementById('calGrid');
  if (!dataEl || !grid) return;

  const titleEl = document.getElementById('calTitle');
  const prevBtn = document.getElementById('calPrev');
  const nextBtn = document.getElementById('calNext');
  const locale = document.documentElement.lang || 'hu';
  const colorPalette = ['#7c4dff', '#18b38a', '#ff8a3d', '#ff5c8a', '#4da3ff', '#ffd166'];
  let events = [];
  try {
    const payload = JSON.parse(dataEl.textContent || '{}');
    events = Array.isArray(payload.events) ? payload.events : [];
  } catch {}
  let view = new Date();
  let mode = 'month';

  events = events.map((ev, index) => ({
    ...ev,
    start_at: ev.start_at || (ev.date ? `${ev.date} 00:00:00` : ''),
    end_at: ev.end_at || (ev.date ? `${ev.date} 23:59:59` : ''),
    color: ev.color || colorPalette[index % colorPalette.length],
  }));

  function fmtTitle(date) {
    return new Intl.DateTimeFormat(locale, { year: 'numeric', month: 'long' }).format(date).replace(/^./, (char) => char.toUpperCase());
  }
  function daysInMonth(year, month) { return new Date(year, month + 1, 0).getDate(); }
  function mondayIndex(day) { return (day + 6) % 7; }
  function dateKey(date) { return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`; }
  function parseDateTime(value) {
    if (!value) return null;
    const parsed = new Date(String(value).replace(' ', 'T'));
    return Number.isNaN(parsed.getTime()) ? null : parsed;
  }
  function getEventsForDay(dateObj) {
    const start = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), 0, 0, 0, 0);
    const end = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), 23, 59, 59, 999);
    return events.filter((eventItem) => {
      const eventStart = parseDateTime(eventItem.start_at);
      const eventEnd = parseDateTime(eventItem.end_at || eventItem.start_at);
      return !!eventStart && !!eventEnd && eventStart <= end && eventEnd >= start;
    });
  }
  function formatEventTimeRange(ev) {
    const start = parseDateTime(ev.start_at);
    const end = parseDateTime(ev.end_at || ev.start_at);
    if (!start || !end) return '';
    const sameDay = dateKey(start) === dateKey(end);
    const dateFmt = new Intl.DateTimeFormat(locale, { year: 'numeric', month: 'short', day: 'numeric' });
    const timeFmt = new Intl.DateTimeFormat(locale, { hour: '2-digit', minute: '2-digit' });
    if (sameDay) return `${dateFmt.format(start)} ${timeFmt.format(start)} - ${timeFmt.format(end)}`;
    return `${dateFmt.format(start)} ${timeFmt.format(start)} - ${dateFmt.format(end)} ${timeFmt.format(end)}`;
  }
  function setGridCols(count) {
    grid.classList.remove('cols-1', 'cols-7');
    grid.classList.add(count === 1 ? 'cols-1' : 'cols-7');
  }
  function openEventModal(list, focusId) {
    let modal = document.getElementById('eventModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.className = 'modal fade';
      modal.id = 'eventModal';
      modal.tabIndex = -1;
      modal.setAttribute('aria-hidden', 'true');
      modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content modalDark">
            <div class="modal-header border-0">
              <h5 class="modal-title">${window.___ ? window.___('Event') : 'Event'}</h5>
              <button type="button" class="btn-close btnCloseWhite" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="eventModalBody"></div>
          </div>
        </div>`;
      document.body.appendChild(modal);
    }
    const body = modal.querySelector('#eventModalBody');
    body.innerHTML = '';
    const ordered = [...list].sort((a, b) => {
      const aTime = parseDateTime(a.start_at)?.getTime() || 0;
      const bTime = parseDateTime(b.start_at)?.getTime() || 0;
      if (focusId) {
        if (String(a.id) === String(focusId)) return -1;
        if (String(b.id) === String(focusId)) return 1;
      }
      return aTime - bTime;
    });
    ordered.forEach((eventItem) => {
      const item = document.createElement('div');
      item.className = 'mb-3 p-3 rounded border';
      item.style.borderColor = eventItem.color || '#7c4dff';
      item.innerHTML = `<div class="d-flex align-items-center gap-2 mb-1"><span class="calendarEventDot" style="background:${eventItem.color || '#7c4dff'}"></span><div class="h6 mb-0">${eventItem.title || (window.___ ? window.___('Event') : 'Event')}</div></div><div class="text-muted small mb-2">${formatEventTimeRange(eventItem)}</div><div class="text-muted small mb-2">${eventItem.description || ''}</div>`;
      if (eventItem.href) {
        const anchor = document.createElement('a');
        anchor.href = eventItem.href;
        anchor.className = 'btn btnPrimary btn-sm';
        anchor.textContent = window.___ ? window.___('Open') : 'Open';
        item.appendChild(anchor);
      }
      body.appendChild(item);
    });
    if (window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(modal).show();
    } else {
      modal.style.display = 'block';
    }
  }
  function buildDayCell(dateObj, today) {
    const cell = document.createElement('div');
    const isWeekend = [0, 6].includes(dateObj.getDay());
    const isToday = dateObj.getFullYear() === today.getFullYear() && dateObj.getMonth() === today.getMonth() && dateObj.getDate() === today.getDate();
    cell.className = 'calendarCell' + (isWeekend ? ' weekend' : '') + (isToday ? ' today' : '');
    const dateNum = document.createElement('div');
    dateNum.className = 'dateNum';
    dateNum.textContent = String(dateObj.getDate());
    cell.appendChild(dateNum);
    const dayEvents = getEventsForDay(dateObj);
    if (dayEvents.length) {
      const wrap = document.createElement('div');
      wrap.className = 'calendarEventList';
      if (dayEvents.length <= 2) {
        dayEvents.forEach((eventItem) => {
          const chip = document.createElement('button');
          chip.type = 'button';
          chip.className = 'eventBadge hiTech calendarEventChip';
          chip.style.borderColor = eventItem.color;
          chip.style.background = `${eventItem.color}22`;
          chip.textContent = eventItem.title || (window.___ ? window.___('Event') : 'Event');
          chip.addEventListener('click', (event) => {
            event.stopPropagation();
            openEventModal(dayEvents, eventItem.id || null);
          });
          wrap.appendChild(chip);
        });
      } else {
        const dots = document.createElement('div');
        dots.className = 'calendarEventDots';
        dayEvents.forEach((eventItem) => {
          const dot = document.createElement('button');
          dot.type = 'button';
          dot.className = 'calendarEventDot';
          dot.style.background = eventItem.color;
          dot.title = eventItem.title || (window.___ ? window.___('Event') : 'Event');
          dot.addEventListener('click', (event) => {
            event.stopPropagation();
            openEventModal(dayEvents, eventItem.id || null);
          });
          dots.appendChild(dot);
        });
        wrap.appendChild(dots);
      }
      cell.appendChild(wrap);
      cell.classList.add('hasEvent');
      cell.addEventListener('click', () => openEventModal(dayEvents, dayEvents[0]?.id || null));
    } else if (window.isAdmin) {
      cell.addEventListener('click', () => {
        const start = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), 18, 0, 0, 0);
        const end = new Date(start.getTime() + 2 * 3600 * 1000);
        const toIsoLocal = (value) => {
          const y = value.getFullYear();
          const m = String(value.getMonth() + 1).padStart(2, '0');
          const d = String(value.getDate()).padStart(2, '0');
          const h = String(value.getHours()).padStart(2, '0');
          const min = String(value.getMinutes()).padStart(2, '0');
          return `${y}-${m}-${d}T${h}:${min}`;
        };
        window.location.href = `/index.php?page=dashboard&tab=events&open_event_editor=1&event_start=${encodeURIComponent(toIsoLocal(start))}&event_end=${encodeURIComponent(toIsoLocal(end))}`;
      });
    }
    grid.appendChild(cell);
  }
  function render() {
    const year = view.getFullYear();
    const month = view.getMonth();
    const today = new Date();
    grid.innerHTML = '';
    if (mode === 'month') {
      titleEl.textContent = fmtTitle(view);
      setGridCols(7);
      const first = new Date(year, month, 1);
      const firstOffset = mondayIndex(first.getDay());
      for (let i = 0; i < firstOffset; i += 1) {
        const placeholder = document.createElement('div');
        placeholder.className = 'calendarCell placeholder';
        grid.appendChild(placeholder);
      }
      for (let day = 1; day <= daysInMonth(year, month); day += 1) {
        buildDayCell(new Date(year, month, day), today);
      }
      return;
    }
    if (mode === 'week') {
      const monday = new Date(view);
      monday.setDate(view.getDate() - ((view.getDay() + 6) % 7));
      const sunday = new Date(monday);
      sunday.setDate(monday.getDate() + 6);
      const formatter = new Intl.DateTimeFormat(locale, { month: 'short', day: 'numeric' });
      titleEl.textContent = `${formatter.format(monday)} - ${formatter.format(sunday)}`;
      setGridCols(7);
      for (let index = 0; index < 7; index += 1) {
        const date = new Date(monday);
        date.setDate(monday.getDate() + index);
        buildDayCell(date, today);
      }
      return;
    }
    titleEl.textContent = new Intl.DateTimeFormat(locale, { year: 'numeric', month: 'long', day: 'numeric' }).format(view);
    setGridCols(1);
    buildDayCell(view, today);
  }
  function step(delta) {
    if (mode === 'month') view.setMonth(view.getMonth() + delta);
    else if (mode === 'week') view.setDate(view.getDate() + (delta * 7));
    else view.setDate(view.getDate() + delta);
    render();
  }
  function setMode(nextMode) {
    mode = nextMode;
    if (mode === 'today') view = new Date();
    [document.getElementById('viewToday'), document.getElementById('viewWeek'), document.getElementById('viewMonth')].forEach((button) => button?.classList.remove('active'));
    if (mode === 'today') document.getElementById('viewToday')?.classList.add('active');
    else if (mode === 'week') document.getElementById('viewWeek')?.classList.add('active');
    else document.getElementById('viewMonth')?.classList.add('active');
    render();
  }

  prevBtn?.addEventListener('click', () => step(-1));
  nextBtn?.addEventListener('click', () => step(1));
  document.getElementById('viewToday')?.addEventListener('click', () => setMode('today'));
  document.getElementById('viewWeek')?.addEventListener('click', () => setMode('week'));
  document.getElementById('viewMonth')?.addEventListener('click', () => setMode('month'));
  render();
})();
