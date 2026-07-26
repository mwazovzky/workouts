import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import { describe, expect, it, vi, beforeEach } from 'vitest';

let lastSubmit;

vi.mock('@/composables/useApiForm', () => ({
  useApiForm: initial => {
    const form = reactive({ ...initial, errors: {}, processing: false });
    form.post = (url, opts) => {
      lastSubmit = { method: 'post', url, data: JSON.parse(JSON.stringify(form.activities)) };
      opts.onSuccess?.({ data: { data: { id: 1 } } });
    };
    form.put = (url, opts) => {
      lastSubmit = { method: 'put', url, data: JSON.parse(JSON.stringify(form.activities)) };
      opts.onSuccess?.({ data: { data: { id: 1 } } });
    };
    return form;
  },
}));

vi.mock('@/composables/useTranslation', () => ({
  useTranslation: () => ({ t: value => value }),
}));

vi.mock('vue-sonner', () => ({ toast: { error: vi.fn(), success: vi.fn() } }));

import WorkoutTemplateForm from '@/Components/Admin/WorkoutTemplateForm.vue';

describe('WorkoutTemplateForm', () => {
  const exercises = [
    {
      id: 1,
      name: 'Bench Press',
      effort_label: 'Reps',
      difficulty_unit: 'kilograms',
      difficulty_label: 'Weight (kg)',
    },
    {
      id: 2,
      name: 'Endurance Ride',
      effort_label: 'Duration',
      difficulty_unit: 'heart_rate_zone',
      difficulty_label: 'Zone',
    },
    {
      id: 3,
      name: 'Plank',
      effort_label: 'Duration',
      difficulty_unit: 'none',
      difficulty_label: '',
    },
  ];

  beforeEach(() => {
    lastSubmit = undefined;
  });

  function buildWrapper(props = {}) {
    return mount(WorkoutTemplateForm, { props: { exercises, ...props } });
  }

  it('adds an activity with a default set', async () => {
    const wrapper = buildWrapper();

    await wrapper.find('button[type="button"]').trigger('click'); // "Add activity"

    expect(wrapper.findAll('select')).not.toHaveLength(0);
    // One activity, one default set row with an effort input
    expect(wrapper.find('input[type="number"]').exists()).toBe(true);
  });

  it('posts to the create endpoint with normalized sequential orders', async () => {
    const wrapper = buildWrapper();

    // add one activity (defaults to first exercise = weight unit)
    await wrapper.get('button').trigger('click');
    await wrapper.find('form').trigger('submit');

    expect(lastSubmit.method).toBe('post');
    expect(lastSubmit.url).toBe('/api/v1/admin/workout-templates');
    expect(lastSubmit.data[0].order).toBe(1);
    expect(lastSubmit.data[0].sets[0].order).toBe(1);
  });

  it('puts to the update endpoint when editing an existing template', async () => {
    const wrapper = buildWrapper({
      template: {
        id: 9,
        translations: { name: { en: 'Full Body' }, description: {} },
        activities: [{ exercise_id: 1, sets: [{ effort_value: 10, difficulty_value: 50 }] }],
      },
    });

    await wrapper.find('form').trigger('submit');

    expect(lastSubmit.method).toBe('put');
    expect(lastSubmit.url).toBe('/api/v1/admin/workout-templates/9');
  });

  it('renders a zone select when the activity uses a heart-rate-zone exercise', async () => {
    const wrapper = buildWrapper({
      template: {
        id: 9,
        translations: { name: { en: 'Ride' }, description: {} },
        activities: [{ exercise_id: 2, sets: [{ effort_value: 1800, difficulty_value: 2 }] }],
      },
    });

    // Two selects: the exercise picker + the zone picker for the set
    const selects = wrapper.findAll('select');
    expect(selects.length).toBe(2);
    // The zone picker has the "—" option plus 1..5
    expect(selects[1].findAll('option')).toHaveLength(6);
  });

  it('renders no difficulty input for a bodyweight (none) exercise', () => {
    const wrapper = buildWrapper({
      template: {
        id: 9,
        translations: { name: { en: 'Core' }, description: {} },
        activities: [{ exercise_id: 3, sets: [{ effort_value: 60, difficulty_value: null }] }],
      },
    });

    // Only the exercise picker select; no zone select
    expect(wrapper.findAll('select')).toHaveLength(1);
    expect(wrapper.find('input[step="0.5"]').exists()).toBe(false);
  });
});
