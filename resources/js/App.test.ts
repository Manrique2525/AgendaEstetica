import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import App from './App.vue';
import router from './router';

describe('App', () => {
    it('mounts the root component with the application router', async () => {
        await router.push('/');
        await router.isReady();

        const wrapper = mount(App, {
            global: {
                plugins: [router],
            },
        });

        expect(wrapper.exists()).toBe(true);
    });
});
