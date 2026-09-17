export interface ApiErrorPayload {
    data?: unknown;
    message?: string;
    code?: string;
    errors?: Record<string, string[]>;
}

export class ApiError extends Error {
    constructor(
        public readonly status: number,
        message: string,
        public readonly code?: string,
        public readonly errors?: Record<string, string[]>,
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

function readCookie(name: string): string | null {
    const value = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith(`${name}=`))
        ?.split('=')[1];

    return value ? decodeURIComponent(value) : null;
}

async function parsePayload(response: Response): Promise<ApiErrorPayload | undefined> {
    if (response.status === 204) {
        return undefined;
    }

    const contentType = response.headers.get('content-type') ?? '';

    if (!contentType.includes('application/json')) {
        return undefined;
    }

    return response.json() as Promise<ApiErrorPayload>;
}

async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
    const method = (init.method ?? 'GET').toUpperCase();
    const headers = new Headers(init.headers);

    headers.set('Accept', 'application/json');

    if (init.body && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }

    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
        const csrfToken = readCookie('XSRF-TOKEN');

        if (csrfToken) {
            headers.set('X-XSRF-TOKEN', csrfToken);
        }
    }

    const response = await fetch(`/api/v1${path}`, {
        ...init,
        credentials: 'include',
        headers,
    });
    const payload = await parsePayload(response);

    if (!response.ok) {
        throw new ApiError(
            response.status,
            payload?.message ?? 'La solicitud no pudo completarse.',
            payload?.code,
            payload?.errors,
        );
    }

    return payload?.data as T;
}

export const http = {
    async csrf(): Promise<void> {
        const response = await fetch('/sanctum/csrf-cookie', {
            credentials: 'include',
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new ApiError(response.status, 'No fue posible inicializar CSRF.');
        }
    },

    get<T>(path: string): Promise<T> {
        return request<T>(path);
    },

    post<T>(path: string, body?: unknown, requestHeaders?: HeadersInit): Promise<T> {
        return request<T>(path, {
            method: 'POST',
            body: body === undefined ? undefined : JSON.stringify(body),
            headers: requestHeaders,
        });
    },
};
