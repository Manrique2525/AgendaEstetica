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

function localParts(instant: number, timezone: string): string {
    const parts = Object.fromEntries(new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hourCycle: 'h23',
    }).formatToParts(new Date(instant)).map(({ type, value }) => [type, value]));

    return `${parts.year}-${parts.month}-${parts.day}T${parts.hour}:${parts.minute}:${parts.second}`;
}

export function businessDateFromInstant(instant: string, timezone: string): string {
    return partsToDate(new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).formatToParts(new Date(instant)));
}

export function resolveLocalDateTime(date: string, time: string, timezone: string): string {
    const localText = `${date}T${time}:00`;
    const localAsUtc = Date.parse(`${localText}Z`);
    const matches: number[] = [];

    for (let offsetMinutes = -14 * 60; offsetMinutes <= 14 * 60; offsetMinutes += 15) {
        const candidate = localAsUtc - offsetMinutes * 60_000;

        if (localParts(candidate, timezone) === localText && !matches.includes(candidate)) {
            matches.push(candidate);
        }
    }

    if (matches.length !== 1) {
        throw new Error('La hora seleccionada no es única en la zona horaria del negocio.');
    }

    return new Date(matches[0]).toISOString();
}
