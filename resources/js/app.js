import './bootstrap.js';

(function () {
    const prefetchCache = new Map();
    const bar = document.getElementById('nprogress-bar');
    const skeleton = document.getElementById('page-skeleton');
    let skeletonTimer = null;

    function isDownloadHref(href) {
        if (!href) return false;
        return /\/(export|download)(\/|$|\?)/i.test(href) || /\.(xlsx|xls|csv|pdf|zip)(\?|$)/i.test(href);
    }

    function prefetch(href) {
        if (prefetchCache.has(href)) return;
        const ctrl = new AbortController();
        prefetchCache.set(href, ctrl);
        fetch(href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: ctrl.signal
        }).then(r => {
            const ct = r.headers.get('content-type') || '';
            if (r.headers.get('content-disposition')) return null;
            if (r.ok && ct.includes('text/html')) return r.text();
        }).then(html => {
            if (html) prefetchCache.set(href, html);
        }).catch(() => {});
    }

    function showProgress() {
        if (bar) {
            bar.classList.remove('done');
            bar.classList.add('active');
            bar.style.width = '0%';
            requestAnimationFrame(() => { bar.style.width = '30%'; });
        }
    }

    function showSkeleton() {
        if (skeleton) skeleton.classList.add('active');
    }

    function hideProgress() {
        if (bar) {
            bar.classList.add('done');
            setTimeout(() => {
                bar.classList.remove('active', 'done');
                bar.style.width = '0%';
            }, 500);
        }
    }

    function hideSkeleton() {
        if (skeleton) skeleton.classList.remove('active');
    }

    function navigate(href) {
        if (window.location.href === href) return;

        const cached = prefetchCache.get(href);
        if (cached && typeof cached === 'string') {
            showProgress();
            bar.style.width = '100%';
            setTimeout(() => applyHTML(cached, href), 50);
            return;
        }

        showProgress();
        skeletonTimer = setTimeout(showSkeleton, 150);

        fetch(href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => {
            if (r.redirected) {
                hideSkeleton();
                hideProgress();
                window.location.href = r.url;
                return null;
            }
            if (!r.ok) {
                hideSkeleton();
                hideProgress();
                window.location.href = href;
                return null;
            }
            const ct = r.headers.get('content-type') || '';
            if (!ct.includes('text/html') || r.headers.get('content-disposition')) {
                // Bukan halaman HTML / response unduhan: serahkan ke browser.
                hideSkeleton();
                hideProgress();
                clearTimeout(skeletonTimer);
                window.location.href = href;
                return null;
            }
            return r.text();
        }).then(html => {
            if (html) {
                bar.style.width = '100%';
                clearTimeout(skeletonTimer);
                applyHTML(html, href);
            }
        }).catch(() => {
            clearTimeout(skeletonTimer);
            hideSkeleton();
            hideProgress();
            window.location.href = href;
        });
    }

    function applyHTML(html, href) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newBody = doc.body;
        const newTitle = doc.querySelector('title');

        document.body.innerHTML = newBody.innerHTML;
        if (newTitle) document.title = newTitle.textContent;

        window.history.pushState({}, '', href);
        hideSkeleton();
        hideProgress();

        document.querySelectorAll('script').forEach(s => {
            const newScript = document.createElement('script');
            if (s.src) newScript.src = s.src;
            else newScript.textContent = s.textContent;
            document.body.appendChild(newScript);
        });

        if (typeof window.initPage === 'function') {
            window.initPage();
        }

        window.scrollTo(0, 0);
        prefetchCache.delete(href);
    }

    document.addEventListener('mouseover', function (e) {
        const a = e.target.closest('a[href]');
        if (!a) return;
        if (a.hasAttribute('download')) return;
        if (isDownloadHref(a.getAttribute('href'))) return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || a.target === '_blank') return;
        if (href.startsWith('http') && !href.startsWith(window.location.origin)) return;
        prefetch(href);
    });

    document.addEventListener('click', function (e) {
        const a = e.target.closest('a[href]');
        if (!a) return;
        if (a.hasAttribute('download')) return;
        const href = a.getAttribute('href');
        if (isDownloadHref(href)) return;
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || a.target === '_blank') return;
        if (href.startsWith('http') && !href.startsWith(window.location.origin)) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey) return;

        e.preventDefault();
        navigate(href);
    });

    window.addEventListener('popstate', function () {
        navigate(window.location.href);
    });
})();
