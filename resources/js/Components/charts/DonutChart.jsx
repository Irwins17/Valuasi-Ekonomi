import { Doughnut } from 'react-chartjs-2';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

const DEFAULT_COLORS = ['#6366f1', '#06d6a0', '#f72585'];

export default function DonutChart({ labels, data, colors = DEFAULT_COLORS, cutout = '65%', showLegend = false, height }) {
    return (
        <Doughnut
            data={{
                labels,
                datasets: [
                    {
                        data,
                        backgroundColor: colors,
                        borderWidth: 0,
                        borderRadius: 4,
                        spacing: 2,
                    },
                ],
            }}
            height={height}
            options={{
                responsive: true,
                maintainAspectRatio: false,
                cutout,
                plugins: {
                    legend: {
                        display: showLegend,
                        position: 'bottom',
                        labels: { font: { family: 'Inter', size: 11 }, padding: 12 },
                    },
                },
                animation: { animateRotate: true, duration: 1500 },
            }}
        />
    );
}
