(() => {
  const ctx = document.getElementById('weightChart');
  if (!ctx || typeof Chart === 'undefined') {
    return;
  }

  const isMobile = window.matchMedia('(max-width: 640px)').matches;
  // eslint-disable-next-line no-new
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Week 0', 'Week 4', 'Week 8', 'Week 12', 'Week 16', 'Week 20', 'Week 24'],
      datasets: [{
        data: [0, -3.2, -6.1, -8.5, -11.2, -13.1, -15],
        borderColor: '#1A56E8',
        backgroundColor: (context) => {
          const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 260);
          gradient.addColorStop(0, 'rgba(26,86,232,0.12)');
          gradient.addColorStop(1, 'rgba(26,86,232,0)');
          return gradient;
        },
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#1A56E8',
        pointBorderColor: '#fff',
        pointBorderWidth: 3,
        pointRadius: 6,
        pointHoverRadius: 9,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 1200, easing: 'easeOutQuart' },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#141929',
          titleFont: { family: 'Outfit', size: 13, weight: '600' },
          bodyFont: { family: 'Outfit', size: 14, weight: '700' },
          padding: 14,
          cornerRadius: 12,
          displayColors: false,
          callbacks: { label: (c) => `${c.parsed.y}% body weight` },
        },
      },
      scales: {
        y: {
          min: -18,
          max: 2,
          ticks: {
            callback: (value) => `${value}%`,
            font: { family: 'Outfit', size: isMobile ? 10 : 12 },
            color: '#9BA3B9',
            padding: isMobile ? 4 : 8,
          },
          grid: { color: '#ECEEF5' },
          border: { display: false },
        },
        x: {
          ticks: {
            autoSkip: true,
            maxTicksLimit: isMobile ? 4 : 7,
            maxRotation: 0,
            minRotation: 0,
            font: { family: 'Outfit', size: isMobile ? 10 : 12 },
            color: '#9BA3B9',
            padding: isMobile ? 4 : 8,
          },
          grid: { display: false },
          border: { display: false },
        },
      },
    },
  });
})();
