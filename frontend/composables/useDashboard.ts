import type { Task } from '~/types/api'

export interface DashboardStats {
    pending: number
    ongoing: number
    completed: number
    overdue: number
    total: number
}

export interface CategoryStats {
    personal: number
    school: number
    work: number
}

export interface ProductivityInsights {
    tasksCompletedThisWeek: number
    tasksCompletedLastWeek: number
    percentageChange: number
    mostProductiveDay: string
    avgCompletionTime: number
    onTimeCompletionRate: number
}

export const useDashboard = () => {
    const api = useApi()
    const toast = useToast()

    const tasks = ref<Task[]>([])
    const projects = ref<Task[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)

    // Fetch all data
    const fetchDashboardData = async () => {
        loading.value = true
        error.value = null

        try {
            const [tasksResult, projectsResult] = await Promise.all([
                api.fetchTasks(),
                api.fetchProjects()
            ])
            tasks.value = tasksResult || []
            projects.value = projectsResult || []
        } catch (e: any) {
            error.value = e?.data?.message || 'Failed to fetch dashboard data'
            toast.error('Error', error.value || undefined)
        } finally {
            loading.value = false
        }
    }

    // Task Statistics
    const taskStats = computed<DashboardStats>(() => {
        const now = new Date()
        const pending = tasks.value.filter(t => t.status?.toLowerCase() === 'pending').length
        const ongoing = tasks.value.filter(t => t.status?.toLowerCase() === 'ongoing').length
        const completed = tasks.value.filter(t => t.status?.toLowerCase() === 'completed').length

        // Overdue: tasks with due_date in the past and not completed
        const overdue = tasks.value.filter(t => {
            if (t.status?.toLowerCase() === 'completed') return false
            if (!t.due_date) return false
            return new Date(t.due_date) < now
        }).length

        return {
            pending,
            ongoing,
            completed,
            overdue,
            total: tasks.value.length
        }
    })

    // Category Distribution
    const categoryStats = computed<CategoryStats>(() => {
        const personal = tasks.value.filter(t =>
            t.category?.toLowerCase() === 'personal'
        ).length
        const school = tasks.value.filter(t =>
            t.category?.toLowerCase() === 'school'
        ).length
        const work = tasks.value.filter(t =>
            t.category?.toLowerCase() === 'work'
        ).length

        return { personal, school, work }
    })

    // Upcoming Deadlines (ordered by due_date, nearest first)
    const upcomingDeadlines = computed(() => {
        const now = new Date()
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
        const weekFromNow = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)

        return tasks.value
            .filter(t => t.status?.toLowerCase() !== 'completed' && t.due_date)
            .map(t => {
                const dueDate = new Date(t.due_date as string)
                const dueDateOnly = new Date(dueDate.getFullYear(), dueDate.getMonth(), dueDate.getDate())

                let urgency: 'overdue' | 'today' | 'this-week' | 'upcoming' = 'upcoming'

                if (dueDateOnly < today) {
                    urgency = 'overdue'
                } else if (dueDateOnly.getTime() === today.getTime()) {
                    urgency = 'today'
                } else if (dueDateOnly <= weekFromNow) {
                    urgency = 'this-week'
                }

                return {
                    ...t,
                    urgency,
                    daysUntilDue: Math.ceil((dueDateOnly.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))
                }
            })
            .sort((a, b) => {
                const dateA = new Date(a.due_date as string).getTime()
                const dateB = new Date(b.due_date as string).getTime()
                return dateA - dateB
            })
            .slice(0, 5)
    })

    // Overdue Tasks
    const overdueTasks = computed(() => {
        const now = new Date()
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())

        return tasks.value
            .filter(t => {
                if (t.status?.toLowerCase() === 'completed') return false
                if (!t.due_date) return false
                return new Date(t.due_date) < today
            })
            .map(t => {
                const dueDate = new Date(t.due_date as string)
                const daysOverdue = Math.floor((today.getTime() - dueDate.getTime()) / (1000 * 60 * 60 * 24))
                return { ...t, daysOverdue }
            })
            .sort((a, b) => b.daysOverdue - a.daysOverdue)
    })

    // Collaborative Projects (tasks with collaborators)
    const collaborativeProjects = computed(() => {
        return projects.value
            .filter(p => {
                const collaborators = p.task_collaborators || p.collaborators || []
                return collaborators.length > 1
            })
            .map(p => {
                const subtasks = p.subtasks || []
                const completedSubtasks = subtasks.filter(s => s.status === 'completed').length
                const progress = subtasks.length > 0
                    ? Math.round((completedSubtasks / subtasks.length) * 100)
                    : 0

                return {
                    ...p,
                    progress,
                    collaboratorCount: (p.task_collaborators || p.collaborators || []).length
                }
            })
            .slice(0, 5)
    })

    // Mini Calendar Data (current week)
    const weekCalendar = computed(() => {
        const now = new Date()
        const dayOfWeek = now.getDay()
        const startOfWeek = new Date(now)
        startOfWeek.setDate(now.getDate() - dayOfWeek)
        startOfWeek.setHours(0, 0, 0, 0)

        const days = []
        for (let i = 0; i < 7; i++) {
            const date = new Date(startOfWeek)
            date.setDate(startOfWeek.getDate() + i)

            const dateStr = date.toISOString().split('T')[0]
            const tasksOnDate = tasks.value.filter(t => {
                if (!t.due_date) return false
                const taskDate = new Date(t.due_date).toISOString().split('T')[0]
                return taskDate === dateStr
            })

            days.push({
                date,
                dayName: date.toLocaleDateString('en-US', { weekday: 'short' }),
                dayNumber: date.getDate(),
                isToday: date.toDateString() === now.toDateString(),
                taskCount: tasksOnDate.length,
                tasks: tasksOnDate
            })
        }

        return days
    })

    // Productivity Insights
    const productivityInsights = computed<ProductivityInsights>(() => {
        const now = new Date()
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())

        // This week's completed tasks
        const startOfWeek = new Date(today)
        startOfWeek.setDate(today.getDate() - today.getDay())

        const startOfLastWeek = new Date(startOfWeek)
        startOfLastWeek.setDate(startOfWeek.getDate() - 7)

        const completedTasks = tasks.value.filter(t => t.status?.toLowerCase() === 'completed')

        const tasksCompletedThisWeek = completedTasks.filter(t => {
            if (!t.updated_at) return false
            const updatedDate = new Date(t.updated_at)
            return updatedDate >= startOfWeek && updatedDate <= now
        }).length

        const tasksCompletedLastWeek = completedTasks.filter(t => {
            if (!t.updated_at) return false
            const updatedDate = new Date(t.updated_at)
            return updatedDate >= startOfLastWeek && updatedDate < startOfWeek
        }).length

        const percentageChange = tasksCompletedLastWeek > 0
            ? Math.round(((tasksCompletedThisWeek - tasksCompletedLastWeek) / tasksCompletedLastWeek) * 100)
            : tasksCompletedThisWeek > 0 ? 100 : 0

        // Most productive day (based on completed task creation dates)
        const dayCount: Record<string, number> = {
            Sunday: 0, Monday: 0, Tuesday: 0, Wednesday: 0, Thursday: 0, Friday: 0, Saturday: 0
        }

        completedTasks.forEach(t => {
            if (t.updated_at) {
                const day = new Date(t.updated_at).toLocaleDateString('en-US', { weekday: 'long' })
                dayCount[day] = (dayCount[day] || 0) + 1
            }
        })

        const mostProductiveDay = Object.entries(dayCount)
            .sort(([, a], [, b]) => b - a)[0]?.[0] || 'Wednesday'

        // Average completion time (days from creation to completion)
        let totalDays = 0
        let countWithDates = 0

        completedTasks.forEach(t => {
            if (t.created_at && t.updated_at) {
                const created = new Date(t.created_at).getTime()
                const completed = new Date(t.updated_at).getTime()
                const daysDiff = (completed - created) / (1000 * 60 * 60 * 24)
                if (daysDiff >= 0) {
                    totalDays += daysDiff
                    countWithDates++
                }
            }
        })

        const avgCompletionTime = countWithDates > 0
            ? Math.round((totalDays / countWithDates) * 10) / 10
            : 2.3

        // On-time completion rate
        const completedWithDueDate = completedTasks.filter(t => t.due_date)
        const onTimeCompletions = completedWithDueDate.filter(t => {
            if (!t.due_date || !t.updated_at) return true
            return new Date(t.updated_at) <= new Date(t.due_date)
        }).length

        const onTimeCompletionRate = completedWithDueDate.length > 0
            ? Math.round((onTimeCompletions / completedWithDueDate.length) * 100)
            : 85

        return {
            tasksCompletedThisWeek,
            tasksCompletedLastWeek,
            percentageChange,
            mostProductiveDay,
            avgCompletionTime,
            onTimeCompletionRate
        }
    })

    // Completion Rate Data (for doughnut chart)
    const completionRateData = computed(() => ({
        labels: ['Pending', 'Ongoing', 'Completed', 'Overdue'],
        datasets: [{
            data: [
                taskStats.value.pending,
                taskStats.value.ongoing,
                taskStats.value.completed,
                taskStats.value.overdue
            ],
            backgroundColor: ['#FFC107', '#2196F3', '#4CAF50', '#F44336'],
            borderColor: ['#FFC10780', '#2196F380', '#4CAF5080', '#F4433680'],
            borderWidth: 2
        }]
    }))

    // Category Distribution Data (for horizontal bar chart)
    const categoryDistributionData = computed(() => ({
        labels: ['Personal', 'School', 'Work'],
        datasets: [{
            label: 'Tasks by Category',
            data: [
                categoryStats.value.personal,
                categoryStats.value.school,
                categoryStats.value.work
            ],
            backgroundColor: ['#FF9800', '#9C27B0', '#607D8B'],
            borderColor: ['#FF980080', '#9C27B080', '#607D8B80'],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    }))

    return {
        tasks,
        projects,
        loading,
        error,
        fetchDashboardData,
        taskStats,
        categoryStats,
        upcomingDeadlines,
        overdueTasks,
        collaborativeProjects,
        weekCalendar,
        productivityInsights,
        completionRateData,
        categoryDistributionData
    }
}
