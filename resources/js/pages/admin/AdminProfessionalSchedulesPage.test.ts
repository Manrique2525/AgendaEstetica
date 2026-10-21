import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import router from '../../router';
import { ApiError } from '../../services/http';
import AdminProfessionalSchedulesPage from './AdminProfessionalSchedulesPage.vue';

const api = vi.hoisted(() => ({
    professionals: {
        list: vi.fn(),
        schedules: { list: vi.fn(), replace: vi.fn() },
    },
}));

vi.mock('../../services/api/adminOperations', () => ({ adminOperationsApi: api }));

vi.mock('vue-router', async (importOriginal) => {
    const actual = await importOriginal<typeof import('vue-router')>();

    return {
        ...actual,
        useRoute: () => ({ params: { id: '1' } }),
    };
});

async function mountPage(): Promise<ReturnType<typeof mount>> {
    const wrapper = mount(AdminProfessionalSchedulesPage, { global: { plugins: [router] } });
    await flushPromises();

    return wrapper;
}

const professionalList = [
    { id: 1, name: 'Ana', active: true, services: [], created_at: null, updated_at: null },
];

describe('AdminProfessionalSchedulesPage', () => {
    beforeEach(async () => {
        vi.clearAllMocks();
        api.professionals.list.mockResolvedValue(professionalList);
        api.professionals.schedules.list.mockResolvedValue([
            { id: 1, weekday: 1, starts_at: '09:00', ends_at: '17:00' },
        ]);
        api.professionals.schedules.replace.mockResolvedValue([]);
    });

    it('loads the current schedule for the selected professional', async () => {
        const wrapper = await mountPage();

        expect(wrapper.text()).toContain('Ana');
        expect((wrapper.get('input[type="time"]').element as HTMLInputElement).value).toBe('09:00');
    });

    it('validates that start is before end', async () => {
        const wrapper = await mountPage();

        const timeInputs = wrapper.findAll('input[type="time"]');
        await timeInputs[0].setValue('18:00');
        await timeInputs[1].setValue('09:00');

        const saveButton = wrapper.findAll('button').find((button) => button.text() === 'Guardar horarios')!;
        await saveButton.trigger('click');
        await flushPromises();

        expect(wrapper.text()).toContain('la hora de inicio debe ser anterior');
        expect(api.professionals.schedules.replace).not.toHaveBeenCalled();
    });

    it('asks for confirmation and saves the intervals', async () => {
        const wrapper = await mountPage();

        const saveButton = wrapper.findAll('button').find((button) => button.text() === 'Guardar horarios')!;
        await saveButton.trigger('click');
        await flushPromises();

        expect(wrapper.find('[role="dialog"]').exists()).toBe(true);

        const confirmButton = wrapper.find('[role="dialog"] button:last-child');
        await confirmButton.trigger('click');
        await flushPromises();

        expect(api.professionals.schedules.replace).toHaveBeenCalledWith(1, [
            { weekday: 1, starts_at: '09:00', ends_at: '17:00' },
        ]);
        expect(wrapper.text()).toContain('Horarios actualizados.');
    });

    it('surfaces server validation errors', async () => {
        api.professionals.schedules.replace.mockRejectedValue(
            new ApiError(422, 'The given data was invalid.', undefined, { intervals: ['Los intervalos se solapan.'] }),
        );

        const wrapper = await mountPage();

        const saveButton = wrapper.findAll('button').find((button) => button.text() === 'Guardar horarios')!;
        await saveButton.trigger('click');
        await flushPromises();
        await wrapper.find('[role="dialog"] button:last-child').trigger('click');
        await flushPromises();

        expect(wrapper.text()).toContain('Los intervalos se solapan.');
    });

    it('shows an error when the schedule cannot be loaded', async () => {
        api.professionals.schedules.list.mockRejectedValue(new ApiError(500, 'Server error'));

        const wrapper = await mountPage();

        expect(wrapper.text()).toContain('No pudimos cargar los horarios de este profesional.');
    });
});
