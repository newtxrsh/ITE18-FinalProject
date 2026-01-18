<template>
  <div class="chart-card">
    <div class="chart-card__header">
      <h3 class="chart-card__title">Completion Rate</h3>
      <p class="chart-card__subtitle">Task status distribution</p>
    </div>
    <div class="chart-card__body">
      <div class="chart-wrapper">
        <Doughnut 
          v-if="!loading && hasData" 
          :data="chartData" 
          :options="chartOptions" 
        />
        <div v-else-if="!loading" class="empty-state">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 6v6l4 2"/>
          </svg>
          <p>No task data available</p>
        </div>
        <div v-else class="loading-state">
          <div class="spinner"></div>
        </div>
      </div>
      <div class="legend" v-if="hasData">
        <div class="legend-item" v-for="(item, index) in legendItems" :key="index">
          <span class="legend-color" :style="{ background: item.color }"></span>
          <span class="legend-label">{{ item.label }}</span>
          <span class="legend-value">{{ item.value }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import { Doughnut } from 'vue-chartjs'

ChartJS.register(ArcElement, Tooltip, Legend)

interface Props {
  data: {
    labels: string[]
    datasets: {
      data: number[]
      backgroundColor: string[]
      borderColor: string[]
      borderWidth: number
    }[]
  }
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false
})

const chartData = computed(() => props.data)

const hasData = computed(() => {
  return props.data.datasets[0]?.data.some(d => d > 0)
})

const legendItems = computed(() => {
  const labels = props.data.labels
  const data = props.data.datasets[0]?.data || []
  const colors = props.data.datasets[0]?.backgroundColor || []

  return labels.map((label, index) => ({
    label,
    value: data[index] || 0,
    color: colors[index] || '#ccc'
  }))
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '65%',
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(15, 22, 35, 0.95)',
      titleColor: '#fff',
      bodyColor: 'rgba(255, 255, 255, 0.8)',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 12,
      displayColors: true,
      boxPadding: 6
    }
  },
  animation: {
    animateRotate: true,
    animateScale: true,
    duration: 1200,
    easing: 'easeOutQuart' as const
  }
}
</script>

<style scoped>
.chart-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.chart-card__header {
  margin-bottom: 20px;
}

.chart-card__title {
  font-size: 18px;
  font-weight: 700;
  color: white;
  margin: 0;
}

.chart-card__subtitle {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.5);
  margin: 4px 0 0 0;
}

.chart-card__body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.chart-wrapper {
  flex: 1;
  min-height: 180px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.legend {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  transition: all 0.2s ease;
}

.legend-item:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(4px);
}

.legend-color {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.legend-label {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.7);
  flex: 1;
}

.legend-value {
  font-size: 14px;
  font-weight: 600;
  color: white;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: rgba(255, 255, 255, 0.3);
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
