import { h } from 'vue';
import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import UiFormField from './UiFormField.vue';
import UiInput from './UiInput.vue';

describe('UiFormField', () => {
    it('connects labels, help text and errors to a slotted control', () => {
        const wrapper = mount(UiFormField, {
            props: {
                label: 'Correo',
                id: 'email',
                help: 'Usa tu correo administrativo.',
                error: 'El correo no es válido.',
                required: true,
            },
            slots: {
                default: ({ inputId, describedBy, invalid }: { inputId: string; describedBy?: string; invalid: boolean }) => h(UiInput, {
                    id: inputId,
                    'aria-describedby': describedBy,
                    invalid,
                }),
            },
        });
        const input = wrapper.get('input');

        expect(wrapper.get('label').attributes('for')).toBe('email');
        expect(input.attributes('id')).toBe('email');
        expect(input.attributes('aria-describedby')).toBe('email-help email-error');
        expect(input.attributes('aria-invalid')).toBe('true');
        expect(wrapper.get('#email-help').text()).toContain('Usa tu correo');
        expect(wrapper.get('#email-error').text()).toContain('no es válido');
        expect(wrapper.get('label').text()).toContain('*');
    });

    it('generates a control id when one is not provided', () => {
        const wrapper = mount(UiFormField, {
            props: { label: 'Nombre' },
            slots: {
                default: ({ inputId }: { inputId: string }) => h(UiInput, { id: inputId }),
            },
        });

        expect(wrapper.get('label').attributes('for')).toBe(wrapper.get('input').attributes('id'));
    });
});
