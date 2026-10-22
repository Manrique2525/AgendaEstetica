import { describe, expect, it } from 'vitest';
import { addCalendarDays, businessDateToday, formatBusinessTime } from './adminAgendaTime';

describe('Admin Agenda time utilities', () => {
    it('uses the business timezone for today rather than the browser timezone', () => {
        expect(businessDateToday('America/New_York', new Date('2026-03-08T01:00:00Z'))).toBe('2026-03-07');
    });

    it('adds calendar days without elapsed-hour arithmetic', () => {
        expect(addCalendarDays('2026-03-08', 1)).toBe('2026-03-09');
        expect(addCalendarDays('2026-11-01', 1)).toBe('2026-11-02');
    });

    it('formats UTC instants in the supplied business timezone', () => {
        expect(formatBusinessTime('2026-03-08T05:30:00Z', 'America/New_York')).toBe('00:30');
    });
});
