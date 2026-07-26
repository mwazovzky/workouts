import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import { describe, expect, it, vi, beforeEach } from 'vitest';

let lastSubmit;

vi.mock('@/composables/useApiForm', () => ({
  useApiForm: initial => {
    const form = reactive({ ...initial, errors: {}, processing: false });
    form.post = (url, opts) => {
      lastSubmit = { method: 'post', url };
      opts.onSuccess?.({ data: { data: { id: 1 } } });
    };
    form.put = (url, opts) => {
      lastSubmit = { method: 'put', url };
      opts.onSuccess?.({ data: { data: { id: 1 } } });
    };
    return form;
  },
}));

vi.mock('@/composables/useTranslation', () => ({
  useTranslation: () => ({ t: value => value }),
}));

vi.mock('vue-sonner', () => ({ toast: { error: vi.fn(), success: vi.fn() } }));

import CategoryForm from '@/Components/Admin/CategoryForm.vue';

describe('CategoryForm', () => {
  beforeEach(() => {
    lastSubmit = undefined;
  });

  function buildWrapper(props = {}) {
    return mount(CategoryForm, { props });
  }

  it('posts to the create endpoint when no category is provided', async () => {
    const wrapper = buildWrapper();

    await wrapper.find('form').trigger('submit');

    expect(lastSubmit).toEqual({ method: 'post', url: '/api/v1/admin/categories' });
  });

  it('puts to the update endpoint when editing', async () => {
    const wrapper = buildWrapper({
      category: { id: 7, translations: { name: { en: 'Chest', ru: 'Грудь' } } },
    });

    await wrapper.find('form').trigger('submit');

    expect(lastSubmit).toEqual({ method: 'put', url: '/api/v1/admin/categories/7' });
  });

  it('emits saved with the response data', async () => {
    const wrapper = buildWrapper();

    await wrapper.find('form').trigger('submit');

    expect(wrapper.emitted('saved')).toBeTruthy();
    expect(wrapper.emitted('saved')[0]).toEqual([{ id: 1 }]);
  });

  it('emits cancel when the cancel button is clicked', async () => {
    const wrapper = buildWrapper();

    await wrapper.find('button[type="button"]').trigger('click');

    expect(wrapper.emitted('cancel')).toBeTruthy();
  });
});
