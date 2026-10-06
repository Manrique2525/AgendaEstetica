import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import UiInput from './UiInput.vue';

describe('UiInput', () => {
    it('renders native attributes and updates v-model', async () => {
        const wrapper = mount(UiInput, {
            props: {
                modelValue: '',
                type: 'email',
                id: 'email',
                autocomplete: 'username',
                required: true,
            },
        });
        const input = wrapper.get('input');

        expect(input.attributes('type')).toBe('email');
        expect(input.attributes('autocomplete')).toBe('username');
        expect(input.attributes('required')).toBeDefined();

        await input.setValue('admin@example.invalid');

        const updates = wrapper.emitted('update:modelValue');

        expect(updates?.[updates.length - 1]).toEqual(['admin@example.invalid']);
    });

    it('exposes an invalid state accessibly', () => {
        const wrapper = mount(UiInput, { props: { invalid: true } });

        expect(wrapper.get('input').attributes('aria-invalid')).toBe('true');
        expect(wrapper.get('input').classes()).toContain('border-state-error');
    });

    it('disables the native input', () => {
        const wrapper = mount(UiInput, { props: { disabled: true } });

        expect(wrapper.get('input').attributes('disabled')).toBeDefined();
    });
});
