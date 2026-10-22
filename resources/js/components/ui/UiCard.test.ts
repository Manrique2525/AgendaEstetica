import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import UiCard from './UiCard.vue';

describe('UiCard', () => {
    it('renders a neutral div surface with token classes', () => {
        const wrapper = mount(UiCard, { slots: { default: 'Contenido agrupado' } });

        expect(wrapper.element.tagName).toBe('DIV');
        expect(wrapper.text()).toContain('Contenido agrupado');
        expect(wrapper.classes()).toContain('bg-surface-elevated');
        expect(wrapper.classes()).toContain('border-border-default');
    });
});
