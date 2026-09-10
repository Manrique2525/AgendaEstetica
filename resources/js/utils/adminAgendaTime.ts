function partsToDate(parts: Intl.DateTimeFormatPart[]): string {
    const values = Object.fromEntries(parts.map(({ type, value }) => [type, value]));

    return `${values.year}-${values.month}-${values.day}`;
}

export function businessDateToday(timezone: string, now = new Date()): string {
    return partsToDate(new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).formatToParts(now));
}

export function addCalendarDays(date: string, days: number): string {
    const value = new Date(`${date}T00:00:00Z`);
    value.setUTCDate(value.getUTCDate() + days);

    return value.toISOString().slice(0, 10);
}

export function formatBusinessDate(date: string, timezone: string): string {
    const value = new Date(`${date}T12:00:00Z`);

    return new Intl.DateTimeFormat('es-MX', {
        timeZone: timezone,
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    }).format(value);
}

export function formatBusinessTime(instant: string, timezone: string): string {
    return new Intl.DateTimeFormat('es-MX', {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    }).format(new Date(instant));
}
