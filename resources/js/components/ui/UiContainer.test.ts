import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import UiContainer from './UiContainer.vue';

describe('UiContainer', () => {
    it('renders its slot with the shared container contract', () => {
        const wrapper = mount(UiContainer, { slots: { default: 'Contenido' } });

        expect(wrapper.text()).toContain('Contenido');
        expect(wrapper.classes()).toContain('max-w-3xl');
        expect(wrapper.classes()).toContain('px-6');
    });
});
