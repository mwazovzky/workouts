import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';

vi.mock('@/composables/useTranslation', () => ({
  useTranslation: () => ({ t: value => value }),
}));

import Set from '@/Components/Set.vue';

describe('Set', () => {
  function buildWrapper(props = {}) {
    return mount(Set, {
      props: {
        set: { id: 1, order: 1, effort_value: 1800, difficulty_value: null, is_completed: false },
        editable: true,
        effortType: 'duration',
        ...props,
      },
    });
  }

  it('renders a 1-5 zone select for the heart_rate_zone unit', () => {
    const wrapper = buildWrapper({ difficultyUnit: 'heart_rate_zone' });

    const select = wrapper.find('select');
    expect(select.exists()).toBe(true);
    // Options are "—" plus 1..5
    expect(select.findAll('option')).toHaveLength(6);
    expect(wrapper.find('input[type="number"][step="0.5"]').exists()).toBe(false);
  });

  it('renders a numeric difficulty input for weight units', () => {
    const wrapper = buildWrapper({ difficultyUnit: 'kilograms' });

    expect(wrapper.find('select').exists()).toBe(false);
    // step="0.5" input is the difficulty field (effort input has no step)
    expect(wrapper.find('input[step="0.5"]').exists()).toBe(true);
  });

  it('renders no difficulty field for the none unit', () => {
    const wrapper = buildWrapper({ difficultyUnit: 'none' });

    expect(wrapper.find('select').exists()).toBe(false);
    expect(wrapper.find('input[step="0.5"]').exists()).toBe(false);
  });

  it('emits the chosen zone as difficulty_value', async () => {
    const wrapper = buildWrapper({ difficultyUnit: 'heart_rate_zone' });

    await wrapper.find('select').setValue('2');

    const updates = wrapper.emitted('update');
    expect(updates).toBeTruthy();
    expect(updates.at(-1)[0].difficulty_value).toBe(2);
  });

  it('emits null when the zone is left as "—"', async () => {
    const wrapper = buildWrapper({ difficultyUnit: 'heart_rate_zone' });

    // Change effort to trigger an update while difficulty stays "—" (0 -> null)
    await wrapper.find('input[type="number"]').setValue('1200');

    const updates = wrapper.emitted('update');
    expect(updates.at(-1)[0].difficulty_value).toBeNull();
  });
});
