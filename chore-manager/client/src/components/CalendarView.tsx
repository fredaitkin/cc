import React, { useState, useCallback, useEffect } from 'react';
import {
  Calendar,
  dateFnsLocalizer,
  View,
  Event as RBCEvent,
} from 'react-big-calendar';
import { format, parse, startOfWeek, getDay, parseISO, startOfMonth, endOfMonth, startOfWeek as sowFn, endOfWeek } from 'date-fns';
import { enUS } from 'date-fns/locale';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import { ChoreInstanceRow, Member } from '../types';
import * as api from '../api';
import { categoryColor } from './ChoreList';

const localizer = dateFnsLocalizer({
  format,
  parse,
  startOfWeek: () => startOfWeek(new Date(), { weekStartsOn: 1 }),
  getDay,
  locales: { 'en-US': enUS },
});

interface CalendarEvent extends RBCEvent {
  resource: ChoreInstanceRow;
}

function toDateStr(d: Date): string {
  return format(d, 'yyyy-MM-dd');
}

function getRangeForView(date: Date, view: View): { start: Date; end: Date } {
  if (view === 'month') {
    const start = sowFn(startOfMonth(date), { weekStartsOn: 1 });
    const end = endOfWeek(endOfMonth(date), { weekStartsOn: 1 });
    return { start, end };
  }
  // week view
  const start = startOfWeek(date, { weekStartsOn: 1 });
  const end = endOfWeek(date, { weekStartsOn: 1 });
  return { start, end };
}

interface Props {
  members: Member[];
}

interface EventComponentProps {
  event: CalendarEvent;
}

function EventComponent({ event }: EventComponentProps) {
  const instance = event.resource;
  const done = !!instance.completed_at;
  return (
    <div style={{ opacity: done ? 0.6 : 1 }}>
      <span style={{ textDecoration: done ? 'line-through' : 'none', fontWeight: 600 }}>
        {done ? '✓ ' : ''}{instance.chore_title}
      </span>
      {instance.assigned_member_name && (
        <div style={{ fontSize: '0.75em', marginTop: 1 }}>
          {instance.assigned_member_name}
        </div>
      )}
    </div>
  );
}

export default function CalendarView({ members }: Props) {
  const [view, setView] = useState<View>('month');
  const [date, setDate] = useState(new Date());
  const [events, setEvents] = useState<CalendarEvent[]>([]);

  const fetchEvents = useCallback(async (currentDate: Date, currentView: View) => {
    const { start, end } = getRangeForView(currentDate, currentView);
    try {
      const instances = await api.getInstances(toDateStr(start), toDateStr(end));
      setEvents(
        instances.map(inst => ({
          id: inst.id,
          title: inst.chore_title,
          start: parseISO(inst.due_date),
          end: parseISO(inst.due_date),
          allDay: true,
          resource: inst,
        }))
      );
    } catch (err) {
      console.error('Failed to load instances', err);
    }
  }, []);

  useEffect(() => {
    fetchEvents(date, view);
  }, [date, view, fetchEvents]);

  async function handleSelectEvent(event: CalendarEvent) {
    try {
      await api.toggleComplete(event.resource);
      fetchEvents(date, view);
    } catch (err: any) {
      alert(err.message);
    }
  }

  // Collect unique categories for legend
  const categories = Array.from(new Set(events.map(e => e.resource.category).filter(Boolean)));

  return (
    <div className="calendar-wrapper">
      <div className="calendar-toolbar">
        <div className="view-toggle">
          <button
            className={view === 'month' ? 'btn-primary' : 'btn-secondary'}
            onClick={() => setView('month')}
          >
            Month
          </button>
          <button
            className={view === 'week' ? 'btn-primary' : 'btn-secondary'}
            onClick={() => setView('week')}
          >
            Week
          </button>
        </div>
        <p className="calendar-hint">Click an event to mark it complete / incomplete</p>
      </div>

      <Calendar
        localizer={localizer}
        events={events}
        view={view}
        onView={setView}
        date={date}
        onNavigate={setDate}
        style={{ height: 600 }}
        onSelectEvent={handleSelectEvent}
        eventPropGetter={event => ({
          style: {
            backgroundColor: categoryColor((event as CalendarEvent).resource.category),
            color: '#222',
            border: 'none',
            borderRadius: 4,
          },
        })}
        components={{
          event: EventComponent as any,
        }}
        views={['month', 'week']}
        popup
      />

      {categories.length > 0 && (
        <div className="legend">
          {categories.map(cat => (
            <span key={cat} className="legend-item">
              <span className="legend-dot" style={{ backgroundColor: categoryColor(cat) }} />
              {cat}
            </span>
          ))}
        </div>
      )}
    </div>
  );
}
