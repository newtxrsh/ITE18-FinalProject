<template>
  <div class="insights-card">
    <div class="insights-card__header">
      <div class="header-content">
        <h3 class="insights-card__title">Productivity Insights</h3>
        <span class="insights-card__subtitle">Your performance health check</span>
      </div>
      <div class="health-score" :class="healthScoreClass">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <span>{{ healthScore }}</span>
      </div>
    </div>
    
    <div class="insights-card__body">
      <div v-if="loading" class="loading-state">
        <div class="skeleton-insight" v-for="i in 4" :key="i"></div>
      </div>
      
      <div v-else class="insights-grid">
        <!-- Tasks Completed This Week -->
        <div class="insight-item insight-item--tasks">
          <div class="insight-item__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
          </div>
          <div class="insight-item__content">
            <span class="insight-item__label">Tasks Completed</span>
            <div class="insight-item__value-row">
              <span class="insight-item__value">{{ insights.tasksCompletedThisWeek }}</span>
              <span 
                class="insight-item__trend" 
                :class="insights.percentageChange >= 0 ? 'trend--up' : 'trend--down'"
              >
                <svg v-if="insights.percentageChange >= 0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <path d="M18 15l-6-6-6 6"/>
                </svg>
                <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
                {{ Math.abs(insights.percentageChange) }}%
              </span>
            </div>
            <span class="insight-item__subtitle">vs last week</span>
          </div>
        </div>
        
        <!-- Most Productive Day -->
        <div class="insight-item insight-item--day">
          <div class="insight-item__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <div class="insight-item__content">
            <span class="insight-item__label">Most Productive</span>
            <span class="insight-item__value">{{ insights.mostProductiveDay }}</span>
            <span class="insight-item__subtitle">Peak performance day</span>
          </div>
        </div>
        
        <!-- Average Completion Time -->
        <div class="insight-item insight-item--time">
          <div class="insight-item__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
          </div>
          <div class="insight-item__content">
            <span class="insight-item__label">Avg. Completion</span>
            <div class="insight-item__value-row">
              <span class="insight-item__value">{{ insights.avgCompletionTime }}</span>
              <span class="insight-item__unit">days</span>
            </div>
            <span class="insight-item__subtitle">Task turnaround time</span>
          </div>
        </div>
        
        <!-- On-Time Completion Rate -->
        <div class="insight-item insight-item--rate">
          <div class="insight-item__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          </div>
          <div class="insight-item__content">
            <span class="insight-item__label">On-Time Rate</span>
            <div class="insight-item__value-row">
              <span class="insight-item__value">{{ insights.onTimeCompletionRate }}</span>
              <span class="insight-item__unit">%</span>
            </div>
            <div class="insight-item__bar">
              <div 
                class="insight-item__bar-fill" 
                :style="{ width: `${insights.onTimeCompletionRate}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface ProductivityInsights {
  tasksCompletedThisWeek: number
  tasksCompletedLastWeek: number
  percentageChange: number
  mostProductiveDay: string
  avgCompletionTime: number
  onTimeCompletionRate: number
}

interface Props {
  insights: ProductivityInsights
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false
})

// Calculate overall health score based on metrics
const healthScore = computed(() => {
  const { onTimeCompletionRate, percentageChange, tasksCompletedThisWeek } = props.insights
  
  // Weighted score
  let score = 0
  score += onTimeCompletionRate * 0.4 // 40% weight for on-time rate
  score += Math.min(100, Math.max(0, 50 + percentageChange)) * 0.3 // 30% for trend
  score += Math.min(100, tasksCompletedThisWeek * 10) * 0.3 // 30% for productivity
  
  if (score >= 80) return 'Excellent'
  if (score >= 60) return 'Good'
  if (score >= 40) return 'Fair'
  return 'Needs Work'
})

const healthScoreClass = computed(() => {
  const score = healthScore.value
  if (score === 'Excellent') return 'health-score--excellent'
  if (score === 'Good') return 'health-score--good'
  if (score === 'Fair') return 'health-score--fair'
  return 'health-score--poor'
})
</script>

<style scoped>
.insights-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px;
}

.insights-card__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 24px;
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.insights-card__title {
  font-size: 18px;
  font-weight: 700;
  color: white;
  margin: 0;
}

.insights-card__subtitle {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.5);
}

.health-score {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
}

.health-score--excellent {
  background: rgba(76, 175, 80, 0.15);
  color: #4CAF50;
}

.health-score--good {
  background: rgba(33, 150, 243, 0.15);
  color: #2196F3;
}

.health-score--fair {
  background: rgba(255, 193, 7, 0.15);
  color: #FFC107;
}

.health-score--poor {
  background: rgba(244, 67, 54, 0.15);
  color: #F44336;
}

.insights-card__body {
  display: flex;
  flex-direction: column;
}

.insights-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.insight-item {
  display: flex;
  gap: 14px;
  padding: 18px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  transition: all 0.3s ease;
}

.insight-item:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-2px);
}

.insight-item__icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.insight-item--tasks .insight-item__icon {
  background: linear-gradient(135deg, rgba(76, 175, 80, 0.2) 0%, rgba(56, 142, 60, 0.1) 100%);
  color: #4CAF50;
}

.insight-item--day .insight-item__icon {
  background: linear-gradient(135deg, rgba(156, 39, 176, 0.2) 0%, rgba(123, 31, 162, 0.1) 100%);
  color: #9C27B0;
}

.insight-item--time .insight-item__icon {
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.2) 0%, rgba(25, 118, 210, 0.1) 100%);
  color: #2196F3;
}

.insight-item--rate .insight-item__icon {
  background: linear-gradient(135deg, rgba(255, 152, 0, 0.2) 0%, rgba(245, 124, 0, 0.1) 100%);
  color: #FF9800;
}

.insight-item__content {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
  flex: 1;
}

.insight-item__label {
  font-size: 12px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.insight-item__value-row {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.insight-item__value {
  font-size: 24px;
  font-weight: 700;
  color: white;
  line-height: 1.2;
}

.insight-item__unit {
  font-size: 14px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.5);
}

.insight-item__trend {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 12px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 6px;
}

.trend--up {
  background: rgba(76, 175, 80, 0.15);
  color: #4CAF50;
}

.trend--down {
  background: rgba(244, 67, 54, 0.15);
  color: #F44336;
}

.insight-item__subtitle {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.4);
}

.insight-item__bar {
  height: 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
  overflow: hidden;
  margin-top: 8px;
}

.insight-item__bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #FF9800 0%, #FFB74D 100%);
  border-radius: 3px;
  transition: width 1s ease-out;
}

.loading-state {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.skeleton-insight {
  height: 100px;
  border-radius: 16px;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  from {
    background-position: 200% 0;
  }
  to {
    background-position: -200% 0;
  }
}

@media (max-width: 640px) {
  .insights-grid {
    grid-template-columns: 1fr;
  }
}
</style>
