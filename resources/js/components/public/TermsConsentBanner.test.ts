import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import router from '../../router';
import TermsConsentBanner from './TermsConsentBanner.vue';

const global = { plugins: [router] };
const storageKey = 'yaris.academic-consent.v1';

describe('TermsConsentBanner', () => {
    beforeEach(() => {
        sessionStorage.removeItem(storageKey);
    });

    afterEach(() => {
        vi.restoreAllMocks();
        sessionStorage.removeItem(storageKey);
    });

    it('shows the compact banner when no decision exists', () => {
        const wrapper = mount(TermsConsentBanner, { global });

        expect(wrapper.find('section[aria-labelledby="terms-banner-title"]').exists()).toBe(true);
        expect(wrapper.findAll('button')).toHaveLength(2);
        expect(wrapper.find('a[href="/terminos-condiciones"]').exists()).toBe(true);
    });

    it.each(['accepted', 'rejected'] as const)('hides for an existing %s decision', (decision) => {
        sessionStorage.setItem(storageKey, decision);

        const wrapper = mount(TermsConsentBanner, { global });

        expect(wrapper.find('section[aria-labelledby="terms-banner-title"]').exists()).toBe(false);
    });

    it('stores rejected and hides immediately without a backend request', async () => {
        const wrapper = mount(TermsConsentBanner, { global });

        await wrapper.get('button').trigger('click');

        expect(sessionStorage.getItem(storageKey)).toBe('rejected');
        expect(wrapper.find('section[aria-labelledby="terms-banner-title"]').exists()).toBe(false);
    });

    it('stores accepted and hides immediately while Terms remains a normal link', async () => {
        const wrapper = mount(TermsConsentBanner, { global });

        await wrapper.findAll('button')[1].trigger('click');

        expect(sessionStorage.getItem(storageKey)).toBe('accepted');
        expect(wrapper.find('section[aria-labelledby="terms-banner-title"]').exists()).toBe(false);
    });

    it('treats an unsupported stored value as no decision', () => {
        sessionStorage.setItem(storageKey, 'unexpected');

        const wrapper = mount(TermsConsentBanner, { global });

        expect(wrapper.find('section[aria-labelledby="terms-banner-title"]').exists()).toBe(true);
    });

    it('shows safely when sessionStorage cannot be read', () => {
        vi.spyOn(Storage.prototype, 'getItem').mockImplementation(() => {
            throw new Error('storage unavailable');
        });

        const wrapper = mount(TermsConsentBanner, { global });

        expect(wrapper.find('section[aria-labelledby="terms-banner-title"]').exists()).toBe(true);
    });

    it('remains usable when sessionStorage writes fail', async () => {
        vi.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {
            throw new Error('storage unavailable');
        });
        const wrapper = mount(TermsConsentBanner, { global });

        await wrapper.findAll('button')[1].trigger('click');

        expect(wrapper.find('section[aria-labelledby="terms-banner-title"]').exists()).toBe(false);
    });

    it('preserves the decision when the banner is remounted in the same tab', async () => {
        const first = mount(TermsConsentBanner, { global });

        await first.get('button').trigger('click');
        first.unmount();

        const second = mount(TermsConsentBanner, { global });

        expect(second.find('section[aria-labelledby="terms-banner-title"]').exists()).toBe(false);
    });
});
