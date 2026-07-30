import { Bar } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const DEFAULT_COLORS = ['#6366f1', '#06d6a0', '#f72585'];

export default function BarChart({ labels, data, datasets, colors = DEFAULT_COLORS, height, horizontal = false }) {
    return (
        <Bar
            data={{
                labels,
                datasets: datasets ?? [
                    {
                        data,
                        backgroundColor: colors,
                        borderRadius: 8,
                        borderSkipped: false,
                    },
                ],
            }}
            height={height}
            options={{
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Inter' } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', weight: 600 } } },
                },
                animation: { duration: 1500 },
            }}
        />
    );
}
