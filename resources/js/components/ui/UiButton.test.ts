import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import UiButton from './UiButton.vue';

describe('UiButton', () => {
    it('renders a native button with a safe default type', () => {
        const wrapper = mount(UiButton, { slots: { default: 'Continuar' } });
        const button = wrapper.get('button');

        expect(button.attributes('type')).toBe('button');
        expect(button.text()).toContain('Continuar');
        expect(button.classes()).toContain('bg-action-primary');
    });

    it('supports the secondary variant', () => {
        const wrapper = mount(UiButton, { props: { variant: 'secondary' } });

        expect(wrapper.get('button').classes()).toContain('bg-action-secondary');
    });

    it('makes loading buttons unavailable while preserving their content', async () => {
        const wrapper = mount(UiButton, {
            props: { loading: true },
            slots: { default: 'Guardar' },
        });
        const button = wrapper.get('button');

        expect(button.attributes('disabled')).toBeDefined();
        expect(button.attributes('aria-busy')).toBe('true');
        expect(button.text()).toContain('Guardar');
    });

    it('disables a disabled button natively', () => {
        const wrapper = mount(UiButton, { props: { disabled: true } });

        expect(wrapper.get('button').attributes('disabled')).toBeDefined();
    });
});
