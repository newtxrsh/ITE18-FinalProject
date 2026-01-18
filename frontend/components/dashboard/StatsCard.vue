<template>
  <div 
    class="stats-card"
    :class="[`stats-card--${variant}`, { 'stats-card--animate': animate }]"
  >
    <div class="stats-card__icon">
      <div class="icon-wrapper" :style="{ background: iconBackground }">
        <slot name="icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="9" rx="1"/>
            <rect x="14" y="3" width="7" height="5" rx="1"/>
          </svg>
        </slot>
      </div>
    </div>
    <div class="stats-card__content">
      <span class="stats-card__label">{{ label }}</span>
      <span class="stats-card__value" ref="valueRef">
        {{ animatedValue }}
      </span>
      <span v-if="trend !== undefined" class="stats-card__trend" :class="trendClass">
        <svg v-if="trend > 0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
          <path d="M18 15l-6-6-6 6"/>
        </svg>
        <svg v-else-if="trend < 0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
          <path d="M6 9l6 6 6-6"/>
        </svg>
        <span>{{ Math.abs(trend) }}%</span>
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  label: string
  value: number
  variant?: 'pending' | 'ongoing' | 'completed' | 'overdue' | 'default'
  iconBackground?: string
  trend?: number
  animate?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  animate: true
})

const animatedValue = ref(0)
const valueRef = ref<HTMLElement>()

const trendClass = computed(() => {
  if (props.trend === undefined) return ''
  return props.trend >= 0 ? 'trend--positive' : 'trend--negative'
})

// Animate counter on mount
onMounted(() => {
  if (props.animate) {
    const duration = 1500
    const startTime = Date.now()
    const startValue = 0
    const endValue = props.value

    const animate = () => {
      const elapsed = Date.now() - startTime
      const progress = Math.min(elapsed / duration, 1)
      
      // Easing function (ease-out)
      const easeOut = 1 - Math.pow(1 - progress, 3)
      animatedValue.value = Math.round(startValue + (endValue - startValue) * easeOut)

      if (progress < 1) {
        requestAnimationFrame(animate)
      }
    }

    requestAnimationFrame(animate)
  } else {
    animatedValue.value = props.value
  }
})

// Watch for value changes
watch(() => props.value, (newValue) => {
  if (props.animate) {
    const duration = 800
    const startTime = Date.now()
    const startValue = animatedValue.value

    const animate = () => {
      const elapsed = Date.now() - startTime
      const progress = Math.min(elapsed / duration, 1)
      const easeOut = 1 - Math.pow(1 - progress, 3)
      animatedValue.value = Math.round(startValue + (newValue - startValue) * easeOut)

      if (progress < 1) {
        requestAnimationFrame(animate)
      }
    }

    requestAnimationFrame(animate)
  } else {
    animatedValue.value = newValue
  }
})
</script>

<style scoped>
.stats-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.stats-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--accent-color, #3b82f6);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stats-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
  border-color: rgba(255, 255, 255, 0.2);
}

.stats-card:hover::before {
  opacity: 1;
}

.stats-card--animate {
  animation: fadeInUp 0.6s ease-out both;
}

.stats-card--pending {
  --accent-color: #FFC107;
}

.stats-card--ongoing {
  --accent-color: #2196F3;
}

.stats-card--completed {
  --accent-color: #4CAF50;
}

.stats-card--overdue {
  --accent-color: #F44336;
}

.stats-card__icon {
  flex-shrink: 0;
}

.icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  background: var(--accent-color, linear-gradient(135deg, #3b82f6 0%, #2563eb 100%));
  box-shadow: 0 8px 24px -4px var(--accent-color, rgba(59, 130, 246, 0.4));
}

.stats-card__content {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
  flex: 1;
}

.stats-card__label {
  font-size: 13px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.6);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stats-card__value {
  font-size: 32px;
  font-weight: 700;
  color: white;
  line-height: 1.2;
  letter-spacing: -0.5px;
}

.stats-card__trend {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 8px;
  width: fit-content;
  margin-top: 4px;
}

.trend--positive {
  background: rgba(76, 175, 80, 0.15);
  color: #4CAF50;
}

.trend--negative {
  background: rgba(244, 67, 54, 0.15);
  color: #F44336;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
