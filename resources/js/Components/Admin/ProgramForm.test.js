import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import { describe, expect, it, vi, beforeEach } from 'vitest';

let lastSubmit;

vi.mock('@/composables/useApiForm', () => ({
  useApiForm: initial => {
    const form = reactive({ ...initial, errors: {}, processing: false });
    form.post = (url, opts) => {
      lastSubmit = {
        method: 'post',
        url,
        assignments: JSON.parse(JSON.stringify(form.assignments)),
      };
      opts.onSuccess?.({ data: { data: { id: 1 } } });
    };
    form.put = (url, opts) => {
      lastSubmit = {
        method: 'put',
        url,
        assignments: JSON.parse(JSON.stringify(form.assignments)),
      };
      opts.onSuccess?.({ data: { data: { id: 1 } } });
    };
    return form;
  },
}));

vi.mock('@/composables/useTranslation', () => ({
  useTranslation: () => ({ t: value => value }),
}));

vi.mock('vue-sonner', () => ({ toast: { error: vi.fn(), success: vi.fn() } }));

import ProgramForm from '@/Components/Admin/ProgramForm.vue';

describe('ProgramForm', () => {
  const templates = [
    { id: 1, name: 'Endurance Ride' },
    { id: 2, name: 'Recovery Ride' },
  ];

  beforeEach(() => {
    lastSubmit = undefined;
  });

  function buildWrapper(props = {}) {
    return mount(ProgramForm, { props: { templates, ...props } });
  }

  it('posts to the create endpoint', async () => {
    const wrapper = buildWrapper();

    await wrapper.find('form').trigger('submit');

    expect(lastSubmit.method).toBe('post');
    expect(lastSubmit.url).toBe('/api/v1/admin/programs');
  });

  it('adds a weekday assignment defaulting to the first template and Monday', async () => {
    const wrapper = buildWrapper();

    await wrapper.get('button').trigger('click'); // "Add assignment"
    await wrapper.find('form').trigger('submit');

    expect(lastSubmit.assignments).toEqual([{ workout_template_id: 1, weekday: 'Monday' }]);
  });

  it('puts to the update endpoint and carries existing assignments', async () => {
    const wrapper = buildWrapper({
      program: {
        id: 5,
        translations: { name: { en: 'Beginner' }, description: {} },
        assignments: [{ workout_template_id: 2, weekday: 'Wednesday' }],
      },
    });

    await wrapper.find('form').trigger('submit');

    expect(lastSubmit.method).toBe('put');
    expect(lastSubmit.url).toBe('/api/v1/admin/programs/5');
    expect(lastSubmit.assignments).toEqual([{ workout_template_id: 2, weekday: 'Wednesday' }]);
  });

  it('disables adding assignments when there are no templates', () => {
    const wrapper = buildWrapper({ templates: [] });

    const addButton = wrapper.findAll('button').find(b => b.text() === 'Add assignment');
    expect(addButton.attributes('disabled')).toBeDefined();
  });
});
