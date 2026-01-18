<template>
  <div class="calendar-card">
    <div class="calendar-card__header">
      <h3 class="calendar-card__title">This Week</h3>
      <NuxtLink to="/calendar" class="view-full-link">
        View Full Calendar
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </NuxtLink>
    </div>
    
    <div class="calendar-card__body">
      <div v-if="loading" class="loading-state">
        <div class="skeleton-day" v-for="i in 7" :key="i"></div>
      </div>
      
      <div v-else class="week-strip">
        <div 
          v-for="(day, index) in weekDays" 
          :key="index"
          class="day-item"
          :class="{ 
            'day-item--today': day.isToday,
            'day-item--has-tasks': day.taskCount > 0
          }"
          @click="$emit('select-day', day.date)"
        >
          <span class="day-name">{{ day.dayName }}</span>
          <span class="day-number">{{ day.dayNumber }}</span>
          <div class="day-dots" v-if="day.taskCount > 0">
            <span 
              v-for="i in Math.min(day.taskCount, 3)" 
              :key="i"
              class="dot"
              :class="getDotClass(day.tasks[i - 1])"
            ></span>
          </div>
          <span v-if="day.taskCount > 3" class="task-count">+{{ day.taskCount - 3 }}</span>
        </div>
      </div>
      
      <div v-if="selectedDay" class="day-tasks">
        <h4 class="day-tasks__title">
          Tasks for {{ formatDate(selectedDay.date) }}
        </h4>
        <div v-if="selectedDay.tasks.length > 0" class="day-tasks__list">
          <div 
            v-for="task in selectedDay.tasks" 
            :key="task.task_id || task.id"
            class="task-item"
          >
            <span class="task-status" :class="`task-status--${task.status?.toLowerCase()}`"></span>
            <span class="task-title">{{ task.title }}</span>
          </div>
        </div>
        <div v-else class="day-tasks__empty">
          No tasks scheduled
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Task {
  task_id?: number
  id?: number
  title: string
  status?: string
  category?: string
}

interface DayData {
  date: Date
  dayName: string
  dayNumber: number
  isToday: boolean
  taskCount: number
  tasks: Task[]
}

interface Props {
  weekDays: DayData[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false
})

defineEmits<{
  'select-day': [date: Date]
}>()

const selectedDay = ref<DayData | null>(null)

const getDotClass = (task?: Task) => {
  if (!task) return ''
  const status = task.status?.toLowerCase()
  if (status === 'completed') return 'dot--completed'
  if (status === 'ongoing' || status === 'in_progress') return 'dot--ongoing'
  return 'dot--pending'
}

const formatDate = (date: Date) => {
  return date.toLocaleDateString('en-US', { 
    weekday: 'long', 
    month: 'short', 
    day: 'numeric' 
  })
}

// Show today's tasks by default
watch(() => props.weekDays, (days) => {
  if (days.length > 0 && !selectedDay.value) {
    const today = days.find(d => d.isToday)
    if (today && today.taskCount > 0) {
      selectedDay.value = today
    }
  }
}, { immediate: true })
</script>

<style scoped>
.calendar-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px;
}

.calendar-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.calendar-card__title {
  font-size: 18px;
  font-weight: 700;
  color: white;
  margin: 0;
}

.view-full-link {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #3b82f6;
  text-decoration: none;
  transition: all 0.2s ease;
}

.view-full-link:hover {
  color: #60a5fa;
  gap: 10px;
}

.calendar-card__body {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.week-strip {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.day-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 12px 6px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 90px;
}

.day-item:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
}

.day-item--today {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(37, 99, 235, 0.1) 100%);
  border-color: rgba(59, 130, 246, 0.4);
  box-shadow: 0 4px 20px -4px rgba(59, 130, 246, 0.3);
}

.day-item--today .day-number {
  background: #3b82f6;
  color: white;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.day-name {
  font-size: 11px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.day-number {
  font-size: 16px;
  font-weight: 700;
  color: white;
}

.day-dots {
  display: flex;
  gap: 3px;
  margin-top: 4px;
}

.dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.dot--pending {
  background: #FFC107;
}

.dot--ongoing {
  background: #2196F3;
}

.dot--completed {
  background: #4CAF50;
}

.task-count {
  font-size: 10px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.5);
}

.day-tasks {
  padding: 16px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 14px;
}

.day-tasks__title {
  font-size: 14px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  margin: 0 0 12px 0;
}

.day-tasks__list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.task-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 10px;
}

.task-status {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.task-status--pending {
  background: #FFC107;
}

.task-status--ongoing,
.task-status--in_progress {
  background: #2196F3;
}

.task-status--completed {
  background: #4CAF50;
}

.task-title {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.8);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.day-tasks__empty {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.4);
  text-align: center;
  padding: 12px;
}

.loading-state {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.skeleton-day {
  height: 90px;
  border-radius: 12px;
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
</style>
