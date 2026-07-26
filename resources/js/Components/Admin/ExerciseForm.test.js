import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import { describe, expect, it, vi, beforeEach } from 'vitest';

let lastSubmit;

vi.mock('@/composables/useApiForm', () => ({
  useApiForm: initial => {
    const form = reactive({ ...initial, errors: {}, processing: false });
    form.post = (url, opts) => {
      lastSubmit = { method: 'post', url, data: { ...form } };
      opts.onSuccess?.({ data: { data: { id: 1 } } });
    };
    form.put = (url, opts) => {
      lastSubmit = { method: 'put', url, data: { ...form } };
      opts.onSuccess?.({ data: { data: { id: 1 } } });
    };
    return form;
  },
}));

vi.mock('@/composables/useTranslation', () => ({
  useTranslation: () => ({ t: value => value }),
}));

vi.mock('vue-sonner', () => ({ toast: { error: vi.fn(), success: vi.fn() } }));

import ExerciseForm from '@/Components/Admin/ExerciseForm.vue';

describe('ExerciseForm', () => {
  const equipmentOptions = [
    { id: 1, name: 'Barbell' },
    { id: 2, name: 'Dumbbell' },
  ];
  const categoryOptions = [
    { id: 10, name: 'Chest' },
    { id: 20, name: 'Legs' },
  ];

  beforeEach(() => {
    lastSubmit = undefined;
  });

  function buildWrapper(props = {}) {
    return mount(ExerciseForm, {
      props: { equipmentOptions, categoryOptions, ...props },
    });
  }

  it('posts to the create endpoint with default equipment when creating', async () => {
    const wrapper = buildWrapper();

    await wrapper.find('form').trigger('submit');

    expect(lastSubmit.method).toBe('post');
    expect(lastSubmit.url).toBe('/api/v1/admin/exercises');
    expect(lastSubmit.data.equipment_id).toBe(1);
  });

  it('puts to the update endpoint when editing', async () => {
    const wrapper = buildWrapper({
      exercise: {
        id: 5,
        equipment_id: 2,
        effort_type: 'duration',
        rest_time_seconds: 60,
        category_ids: [20],
        translations: { name: { en: 'Plank' }, description: { en: '' } },
      },
    });

    await wrapper.find('form').trigger('submit');

    expect(lastSubmit.method).toBe('put');
    expect(lastSubmit.url).toBe('/api/v1/admin/exercises/5');
    expect(lastSubmit.data.category_ids).toEqual([20]);
  });

  it('pre-checks categories that belong to the exercise', () => {
    const wrapper = buildWrapper({
      exercise: {
        id: 5,
        equipment_id: 1,
        effort_type: 'repetitions',
        rest_time_seconds: 90,
        category_ids: [10],
        translations: { name: { en: 'Bench' }, description: { en: '' } },
      },
    });

    const checkboxes = wrapper.findAll('input[type="checkbox"]');
    expect(checkboxes[0].element.checked).toBe(true);
    expect(checkboxes[1].element.checked).toBe(false);
  });

  it('toggles a category when its checkbox is clicked', async () => {
    const wrapper = buildWrapper();

    const checkboxes = wrapper.findAll('input[type="checkbox"]');
    await checkboxes[0].setValue(true);

    await wrapper.find('form').trigger('submit');

    expect(lastSubmit.data.category_ids).toContain(10);
  });
});
