import { ref } from 'vue';
import { authApi, type AuthenticatedUser } from '../services/api/auth';
import { ApiError, http } from '../services/http';

const user = ref<AuthenticatedUser | null>(null);
const initialized = ref(false);
const loading = ref(false);

export function useAuth() {
    async function initialize(): Promise<void> {
        if (initialized.value || loading.value) {
            return;
        }

        loading.value = true;

        try {
            user.value = await authApi.me();
        } catch (error) {
            if (!(error instanceof ApiError) || ![401, 419].includes(error.status)) {
                throw error;
            }

            user.value = null;
        } finally {
            initialized.value = true;
            loading.value = false;
        }
    }

    async function login(email: string, password: string): Promise<void> {
        await http.csrf();
        user.value = await authApi.login(email, password);
        initialized.value = true;
    }

    async function logout(): Promise<void> {
        try {
            await authApi.logout();
        } finally {
            user.value = null;
            initialized.value = true;
        }
    }

    return {
        user,
        initialized,
        loading,
        initialize,
        login,
        logout,
    };
}
