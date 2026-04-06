import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;
Alpine.plugin(collapse);

window.dailyEvaluationPage = () => ({
    progress: 0,

    init() {
        this.$nextTick(() => this.updateProgress());
    },

    updateProgress() {
        const inputs = Array.from(
            document.querySelectorAll('input[type="hidden"][name^="attempts["]:not(:disabled)')
        );

        if (!inputs.length) {
            this.progress = 0;
            return;
        }

        const filled = inputs.filter((input) => input.value !== '').length;
        this.progress = Math.round((filled / inputs.length) * 100);
    },
});

window.attemptCell = (initialState = '') => ({
    states: ['', 'red', 'blue', 'yellow', 'green'],
    state: initialState ?? '',

    cycle() {
        const index = this.states.indexOf(this.state);
        this.state = this.states[(index + 1) % this.states.length];
    },

    circleClass() {
        switch (this.state) {
            case 'red':
                return 'border-rose-500 bg-rose-500 shadow-[0_6px_18px_rgba(244,63,94,0.22)]';
            case 'blue':
                return 'border-sky-500 bg-sky-500 shadow-[0_6px_18px_rgba(14,165,233,0.22)]';
            case 'yellow':
                return 'border-amber-400 bg-amber-400 shadow-[0_6px_18px_rgba(251,191,36,0.20)]';
            case 'green':
                return 'border-emerald-500 bg-emerald-500 shadow-[0_6px_18px_rgba(16,185,129,0.22)]';
            default:
                return 'border-slate-300 bg-white';
        }
    },
});

window.weeklyEvaluationPage = () => ({
    progress: 0,
    isDesktop: window.innerWidth >= 1024,

    init() {
        this.setViewport();
        window.addEventListener('resize', () => {
            this.setViewport();
            this.$nextTick(() => this.updateProgress());
        });

        this.$nextTick(() => this.updateProgress());
    },

    setViewport() {
        this.isDesktop = window.innerWidth >= 1024;
    },

    updateProgress() {
        const inputs = Array.from(
            document.querySelectorAll('input[type="hidden"][name^="weeks["]:not(:disabled)')
        );

        if (!inputs.length) {
            this.progress = 0;
            return;
        }

        const filled = inputs.filter((input) => input.value !== '').length;
        this.progress = Math.round((filled / inputs.length) * 100);
    },
});

window.weeklyCell = (initialState = '') => ({
    states: ['', 'red', 'blue', 'yellow', 'green'],
    state: initialState ?? '',

    cycle() {
        const index = this.states.indexOf(this.state);
        this.state = this.states[(index + 1) % this.states.length];
    },

    circleClass() {
        switch (this.state) {
            case 'red':
                return 'border-rose-500 bg-rose-500 shadow-[0_6px_18px_rgba(244,63,94,0.22)]';
            case 'blue':
                return 'border-sky-500 bg-sky-500 shadow-[0_6px_18px_rgba(14,165,233,0.22)]';
            case 'yellow':
                return 'border-amber-400 bg-amber-400 shadow-[0_6px_18px_rgba(251,191,36,0.20)]';
            case 'green':
                return 'border-emerald-500 bg-emerald-500 shadow-[0_6px_18px_rgba(16,185,129,0.22)]';
            default:
                return 'border-slate-300 bg-white';
        }
    },
});

Alpine.start();