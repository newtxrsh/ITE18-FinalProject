<template>
  <div class="dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h1 class="dashboard-title">
          <span class="greeting">{{ greeting }},</span>
          <span class="user-name">{{ userName }}</span>
        </h1>
        <p class="dashboard-subtitle">Here's your productivity overview for today</p>
      </div>
      <div class="header-date">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <span>{{ formattedDate }}</span>
      </div>
    </div>
    
    <!-- Loading Skeleton -->
    <DashboardSkeleton v-if="loading" />
    
    <template v-else>
      <!-- Overdue Alert -->
      <OverdueAlert 
        v-if="overdueTasks.length > 0"
        :overdue-tasks="overdueTasks"
        @reschedule="handleReschedule"
        @dismiss="dismissOverdueAlert"
        class="animate-section"
      />
      
      <!-- Task Statistics Row -->
      <div class="stats-row animate-section" style="animation-delay: 100ms">
        <StatsCard 
          label="Pending"
          :value="taskStats.pending"
          variant="pending"
          icon-background="linear-gradient(135deg, #FFC107 0%, #FF9800 100%)"
        >
          <template #icon>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </template>
        </StatsCard>
        
        <StatsCard 
          label="Ongoing"
          :value="taskStats.ongoing"
          variant="ongoing"
          icon-background="linear-gradient(135deg, #2196F3 0%, #1976D2 100%)"
        >
          <template #icon>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <polyline points="8 11 10 13 16 9"/>
            </svg>
          </template>
        </StatsCard>
        
        <StatsCard 
          label="Completed"
          :value="taskStats.completed"
          variant="completed"
          icon-background="linear-gradient(135deg, #4CAF50 0%, #388E3C 100%)"
        >
          <template #icon>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
          </template>
        </StatsCard>
        
        <StatsCard 
          label="Overdue"
          :value="taskStats.overdue"
          variant="overdue"
          icon-background="linear-gradient(135deg, #F44336 0%, #D32F2F 100%)"
        >
          <template #icon>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </template>
        </StatsCard>
      </div>
      
      <!-- Charts Row -->
      <div class="charts-row animate-section" style="animation-delay: 200ms">
        <CompletionRateChart 
          :data="completionRateData" 
          :loading="loading"
        />
        <CategoryChart 
          :data="categoryDistributionData" 
          :loading="loading"
        />
      </div>
      
      <!-- Main Content Row -->
      <div class="content-row animate-section" style="animation-delay: 300ms">
        <UpcomingDeadlines 
          :deadlines="upcomingDeadlines"
          :loading="loading"
          @complete="handleCompleteTask"
          @view="handleViewTask"
        />
        <CollaborativeProjects 
          :projects="collaborativeProjects"
          :loading="loading"
          @view="handleViewProject"
        />
      </div>
      
      <!-- Calendar and Insights Row -->
      <div class="bottom-row animate-section" style="animation-delay: 400ms">
        <MiniCalendar 
          :week-days="weekCalendar"
          :loading="loading"
          @select-day="handleSelectDay"
        />
        <ProductivityInsights 
          :insights="productivityInsights"
          :loading="loading"
        />
      </div>
    </template>
    
    <!-- Task Details Modal -->
    <Transition name="modal">
      <TaskModal 
        v-if="selectedTask"
        :task="selectedTask"
        @close="selectedTask = null"
        @update="handleTaskUpdate"
        @delete="handleTaskDelete"
      />
    </Transition>
  </div>
</template>

<script setup lang="ts">
// SEO Meta
useHead({
  title: 'Dashboard',
  meta: [
    { name: 'description', content: 'View your task management dashboard with statistics, upcoming deadlines, and productivity insights.' },
    { property: 'og:title', content: 'Dashboard | Listed Task Manager' },
    { property: 'og:description', content: 'Your productivity command center for task management.' },
  ],
})

const { 
  loading,
  taskStats,
  upcomingDeadlines,
  overdueTasks,
  collaborativeProjects,
  weekCalendar,
  productivityInsights,
  completionRateData,
  categoryDistributionData,
  fetchDashboardData
} = useDashboard()

const authStore = useAuthStore()
const { updateTask, deleteTask } = useApi()
const toast = useToast()
const router = useRouter()

// State
const selectedTask = ref<any>(null)
const showOverdueAlert = ref(true)

// Computed
const userName = computed(() => {
  // Use first_name for greetings, fallback to userName if first_name not available
  return authStore.user?.first_name || authStore.userName || 'there'
})

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Good morning'
  if (hour < 17) return 'Good afternoon'
  return 'Good evening'
})

const formattedDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

// Methods
const handleCompleteTask = async (taskId: number) => {
  try {
    await updateTask(taskId, { status: 'completed' })
    toast.success('Task marked as complete!')
    await fetchDashboardData()
  } catch (error) {
    console.error('Failed to complete task:', error)
    toast.error('Failed to complete task')
  }
}

const handleViewTask = (task: any) => {
  selectedTask.value = task
}

const handleViewProject = (project: any) => {
  router.push(`/projects?id=${project.task_id || project.id}`)
}

const handleReschedule = (task: any) => {
  selectedTask.value = task
}

const dismissOverdueAlert = () => {
  showOverdueAlert.value = false
}

const handleSelectDay = (date: Date) => {
  router.push(`/calendar?date=${date.toISOString().split('T')[0]}`)
}

const handleTaskUpdate = async (taskId: number, data: any) => {
  try {
    await updateTask(taskId, data)
    toast.success('Task updated successfully')
    selectedTask.value = null
    await fetchDashboardData()
  } catch (error) {
    console.error('Failed to update task:', error)
    toast.error('Failed to update task')
  }
}

const handleTaskDelete = async (taskId: number) => {
  try {
    await deleteTask(taskId)
    toast.success('Task deleted successfully')
    selectedTask.value = null
    await fetchDashboardData()
  } catch (error) {
    console.error('Failed to delete task:', error)
    toast.error('Failed to delete task')
  }
}

// Lifecycle
onMounted(() => {
  fetchDashboardData()
})
</script>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding-bottom: 40px;
}

/* Header Styles */
.dashboard-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 8px;
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.dashboard-title {
  font-size: 32px;
  font-weight: 700;
  color: white;
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.greeting {
  color: rgba(255, 255, 255, 0.7);
}

.user-name {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.dashboard-subtitle {
  font-size: 15px;
  color: rgba(255, 255, 255, 0.5);
  margin: 0;
}

.header-date {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
  font-weight: 500;
}

/* Grid Layouts */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.charts-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.content-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.bottom-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

/* Animations */
.animate-section {
  animation: fadeInUp 0.6s ease-out both;
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

/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active > div,
.modal-leave-active > div {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
  transform: scale(0.9);
  opacity: 0;
}

/* Responsive Styles */
@media (max-width: 1280px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .bottom-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1024px) {
  .charts-row,
  .content-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    gap: 16px;
  }
  
  .dashboard-title {
    font-size: 26px;
  }
  
  .header-date {
    width: fit-content;
  }
}

@media (max-width: 640px) {
  .stats-row {
    grid-template-columns: 1fr;
  }
  
  .dashboard-title {
    font-size: 22px;
    flex-direction: column;
    gap: 4px;
  }
}
</style>
