import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import router from '../router';
import UiButton from '../components/ui/UiButton.vue';
import UiCard from '../components/ui/UiCard.vue';
import UiFormField from '../components/ui/UiFormField.vue';
import UiInput from '../components/ui/UiInput.vue';
import AdminLoginPage from './admin/AdminLoginPage.vue';
import AdminPage from './admin/AdminPage.vue';
import FoundationPage from './public/FoundationPage.vue';
import NotFoundPage from './NotFoundPage.vue';

const adminGlobal = { plugins: [router] };

describe('technical page integration', () => {
    it('renders the public foundation surface with the approved card primitive', () => {
        const wrapper = mount(FoundationPage);

        expect(wrapper.findComponent(UiCard).exists()).toBe(true);
        expect(wrapper.text()).toContain('Salón y Barbería Yaris');
        expect(wrapper.text()).toContain('Base visual del sistema');
    });

    it('renders the login form through the approved field and input primitives', () => {
        const wrapper = mount(AdminLoginPage, { global: adminGlobal });

        expect(wrapper.findComponent(UiCard).exists()).toBe(true);
        expect(wrapper.findAllComponents(UiFormField)).toHaveLength(2);
        expect(wrapper.findAllComponents(UiInput)).toHaveLength(2);
        expect(wrapper.findComponent(UiButton).props('type')).toBe('submit');
    });

    it('renders the authenticated technical state without dashboard modules', () => {
        const wrapper = mount(AdminPage, { global: adminGlobal });

        expect(wrapper.findComponent(UiCard).exists()).toBe(true);
        expect(wrapper.findComponent(UiButton).props('variant')).toBe('secondary');
        expect(wrapper.text()).toContain('Sesión activa');
    });

    it('renders the not-found technical surface without business navigation', () => {
        const wrapper = mount(NotFoundPage);

        expect(wrapper.findComponent(UiCard).exists()).toBe(true);
        expect(wrapper.text()).toContain('Página no encontrada');
    });
});
