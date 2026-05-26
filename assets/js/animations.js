/**
 * SMM Panel — Professional animations (vanilla JS)
 */
(function () {
    'use strict';

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ─── Page enter ─── */
    function initPageEnter() {
        document.body.classList.add('anim-body');
        requestAnimationFrame(function () {
            document.body.classList.add('page-entered');
        });
    }

    /* ─── Scroll progress ─── */
    function initScrollProgress() {
        var bar = document.getElementById('scrollProgress');
        if (!bar) return;

        function update() {
            var scrollTop = window.scrollY || document.documentElement.scrollTop;
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            var pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            bar.style.width = pct + '%';
        }

        window.addEventListener('scroll', update, { passive: true });
        update();
    }

    /* ─── Landing navbar scroll ─── */
    function initLandingNav() {
        var nav = document.getElementById('landingNav');
        if (!nav) return;

        function onScroll() {
            nav.classList.toggle('landing-nav-scrolled', window.scrollY > 40);
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ─── Smooth anchor scroll ─── */
    function initSmoothAnchors() {
        document.querySelectorAll('a[href^="#"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var id = link.getAttribute('href');
                if (!id || id === '#') return;
                var target = document.querySelector(id);
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
            });
        });
    }

    /* ─── Button ripple ─── */
    function initRipple() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn');
            if (!btn || btn.disabled) return;

            var rect = btn.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            var ripple = document.createElement('span');
            ripple.className = 'btn-ripple';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            btn.appendChild(ripple);
            ripple.addEventListener('animationend', function () { ripple.remove(); });
        });
    }

    /* ─── Intersection Observer reveals ─── */
    function initReveal() {
        if (reducedMotion) {
            document.querySelectorAll('.anim-reveal, .anim-step, .anim-stagger, .section-header-reveal, .social-proof-bar').forEach(function (el) {
                el.classList.add('revealed');
            });
            document.querySelectorAll('.anim-stat, .anim-row').forEach(function (el) {
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
            var line = document.querySelector('.steps-connector-line');
            if (line) line.classList.add('drawn');
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.anim-reveal, .anim-stagger, .section-header-reveal, .social-proof-bar.anim-reveal-scale').forEach(function (el) {
            observer.observe(el);
        });

        var stepsSection = document.querySelector('.how-it-works-section');
        if (stepsSection) {
            var stepObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    var line = entry.target.querySelector('.steps-connector-line');
                    if (line) line.classList.add('drawn');
                    entry.target.querySelectorAll('.anim-step').forEach(function (step, i) {
                        setTimeout(function () { step.classList.add('revealed'); }, i * 200);
                    });
                    stepObserver.unobserve(entry.target);
                });
            }, { threshold: 0.25 });
            stepObserver.observe(stepsSection);
        }
    }

    /* ─── Count up ─── */
    function animateCount(el, target, decimals, prefix, suffix, duration) {
        var start = 0;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var current = start + (target - start) * eased;

            var formatted = decimals > 0 ? current.toFixed(decimals) : Math.floor(current).toLocaleString();
            el.textContent = (prefix || '') + formatted + (suffix || '');

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                var finalVal = decimals > 0 ? target.toFixed(decimals) : Math.floor(target).toLocaleString();
                el.textContent = (prefix || '') + finalVal + (suffix || '');
            }
        }

        requestAnimationFrame(step);
    }

    function initCountUp() {
        var counters = document.querySelectorAll('[data-count]');
        if (!counters.length) return;

        if (reducedMotion) {
            counters.forEach(function (el) {
                var target = parseFloat(el.dataset.count) || 0;
                var decimals = parseInt(el.dataset.decimals || '0', 10);
                var prefix = el.dataset.prefix || '';
                var suffix = el.dataset.suffix || '';
                var val = decimals > 0 ? target.toFixed(decimals) : Math.floor(target).toLocaleString();
                el.textContent = prefix + val + suffix;
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                if (el.dataset.counted) return;
                el.dataset.counted = '1';

                animateCount(
                    el,
                    parseFloat(el.dataset.count) || 0,
                    parseInt(el.dataset.decimals || '0', 10),
                    el.dataset.prefix || '',
                    el.dataset.suffix || '',
                    parseInt(el.dataset.duration || '1800', 10)
                );
                observer.unobserve(el);
            });
        }, { threshold: 0.5 });

        counters.forEach(function (el) { observer.observe(el); });
    }

    /* ─── Typing effect ─── */
    function initTyping() {
        var el = document.getElementById('typingHeadline');
        if (!el || reducedMotion) {
            if (el) {
                el.textContent = el.dataset.text || el.textContent;
                document.querySelector('.typing-cursor')?.classList.add('hidden');
                document.querySelector('.hero-title-rest')?.classList.add('visible');
                document.querySelector('.hero-premium-sub')?.classList.add('visible');
                document.querySelector('.hero-cta')?.classList.add('visible');
            }
            return;
        }

        var text = el.dataset.text || '';
        var speed = parseInt(el.dataset.speed || '55', 10);
        var i = 0;

        function type() {
            if (i <= text.length) {
                el.textContent = text.slice(0, i);
                i++;
                setTimeout(type, speed);
            } else {
                var cursor = document.querySelector('.typing-cursor');
                if (cursor) cursor.classList.add('hidden');
                document.querySelector('.hero-title-rest')?.classList.add('visible');
                setTimeout(function () {
                    document.querySelector('.hero-premium-sub')?.classList.add('visible');
                }, 200);
                setTimeout(function () {
                    document.querySelector('.hero-cta')?.classList.add('visible');
                }, 450);
            }
        }

        setTimeout(type, 400);
    }

    /* ─── Hero particles (canvas) ─── */
    function initParticles() {
        var canvas = document.getElementById('heroParticles');
        if (!canvas || reducedMotion) return;

        var ctx = canvas.getContext('2d');
        var particles = [];
        var count = 56;
        var animId;

        function resize() {
            canvas.width = canvas.offsetWidth * window.devicePixelRatio;
            canvas.height = canvas.offsetHeight * window.devicePixelRatio;
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }

        function createParticles() {
            particles = [];
            var w = canvas.offsetWidth;
            var h = canvas.offsetHeight;
            for (var i = 0; i < count; i++) {
                particles.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    vx: (Math.random() - 0.5) * 0.35,
                    vy: (Math.random() - 0.5) * 0.35,
                    r: Math.random() * 2.5 + 1.2
                });
            }
        }

        function draw() {
            var w = canvas.offsetWidth;
            var h = canvas.offsetHeight;
            ctx.clearRect(0, 0, w, h);

            particles.forEach(function (p) {
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0 || p.x > w) p.vx *= -1;
                if (p.y < 0 || p.y > h) p.vy *= -1;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(79, 70, 229, 0.55)';
                ctx.fill();
            });

            for (var i = 0; i < particles.length; i++) {
                for (var j = i + 1; j < particles.length; j++) {
                    var dx = particles[i].x - particles[j].x;
                    var dy = particles[i].y - particles[j].y;
                    var dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 110) {
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = 'rgba(79, 70, 229, ' + (0.22 * (1 - dist / 110)) + ')';
                        ctx.lineWidth = 1.2;
                        ctx.stroke();
                    }
                }
            }

            animId = requestAnimationFrame(draw);
        }

        resize();
        createParticles();
        draw();

        window.addEventListener('resize', function () {
            cancelAnimationFrame(animId);
            resize();
            createParticles();
            draw();
        });
    }

    /* ─── Init ─── */
    document.addEventListener('DOMContentLoaded', function () {
        initPageEnter();
        initScrollProgress();
        initLandingNav();
        initSmoothAnchors();
        initRipple();
        initReveal();
        initCountUp();
        initTyping();
        initParticles();
    });
})();
