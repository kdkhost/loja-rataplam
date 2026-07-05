<script>
    (function () {
        var nameInput = document.getElementById('name');
        var slugInput = document.getElementById('slug');
        var titleInput = document.getElementById('seo_title');
        var descriptionInput = document.getElementById('meta_description');
        var keywordInput = document.getElementById('seo_focus_keyword');
        var keywordsInput = document.getElementById('meta_keywords');
        var detailsInput = document.getElementById('details');
        var canonicalInput = document.getElementById('seo_canonical_url');
        var robotsInput = document.getElementById('seo_robots');
        var ogTitleInput = document.getElementById('og_title');
        var ogDescriptionInput = document.getElementById('og_description');
        var ogImageInput = document.getElementById('og_image');
        var twitterTitleInput = document.getElementById('twitter_title');
        var twitterDescriptionInput = document.getElementById('twitter_description');
        var twitterImageInput = document.getElementById('twitter_image');
        var featuredImageInput = document.getElementById('file');
        var featuredImagePreview = document.querySelector('.admin-img.lg');
        var scoreRing = document.querySelector('.seo-score-ring');
        var scoreValue = document.getElementById('seo-score-value');
        var checklist = document.getElementById('seo-checklist');
        var previewTitle = document.getElementById('seo-preview-title');
        var previewUrl = document.getElementById('seo-preview-url');
        var previewDescription = document.getElementById('seo-preview-description');
        var socialTitle = document.getElementById('seo-social-title');
        var socialDescription = document.getElementById('seo-social-description');
        var socialImage = document.getElementById('seo-social-image');

        function value(input) {
            return input ? String(input.value || '').trim() : '';
        }

        function plainText(html) {
            var div = document.createElement('div');
            div.innerHTML = html || '';
            return (div.textContent || div.innerText || '').trim();
        }

        function countKeywords(raw) {
            return raw.replace(/[{}\[\]":]/g, '').split(',').map(function (keyword) {
                return keyword.trim();
            }).filter(Boolean).length;
        }

        function isBetween(length, min, max) {
            return length >= min && length <= max;
        }

        function setCounter(id, count) {
            var counter = document.querySelector('[data-seo-count="' + id + '"]');
            if (counter) counter.textContent = count;
        }

        function check(label, passed, points, hint) {
            return { label: label, passed: passed, points: points, hint: hint };
        }

        function imageUrl(raw) {
            raw = String(raw || '').trim();
            if (!raw) return '';
            if (/^https?:\/\//i.test(raw)) return raw;
            return '{{ url('/core/public/storage/images') }}/' + raw.replace(/^\/+/, '');
        }

        function render() {
            var name = value(nameInput);
            var slug = value(slugInput);
            var title = value(titleInput) || name;
            var description = value(descriptionInput);
            var focusKeyword = value(keywordInput).toLowerCase();
            var keywords = value(keywordsInput);
            var details = plainText(value(detailsInput));
            var robots = value(robotsInput) || 'index,follow';
            var canonical = value(canonicalInput);
            var featuredImageUrl = featuredImagePreview && featuredImagePreview.getAttribute('src') ? featuredImagePreview.getAttribute('src') : '';
            var socialImageUrl = imageUrl(value(twitterImageInput) || value(ogImageInput)) || featuredImageUrl;

            var checks = [
                check('Titulo SEO preenchido', title.length > 0, 10, 'Defina um titulo SEO claro para o produto.'),
                check('Titulo entre 35 e 70 caracteres', isBetween(title.length, 35, 70), 12, 'Ajuste o titulo para ficar entre 35 e 70 caracteres.'),
                check('Descricao SEO preenchida', description.length > 0, 10, 'Preencha a meta description.'),
                check('Descricao entre 120 e 160 caracteres', isBetween(description.length, 120, 160), 16, 'A meta description ideal fica entre 120 e 160 caracteres.'),
                check('Palavra-chave foco definida', focusKeyword.length > 0, 10, 'Informe uma palavra-chave foco.'),
                check('Palavra-chave no titulo', focusKeyword.length > 0 && title.toLowerCase().indexOf(focusKeyword) !== -1, 8, 'Inclua a palavra-chave foco no titulo.'),
                check('Palavra-chave na descricao', focusKeyword.length > 0 && description.toLowerCase().indexOf(focusKeyword) !== -1, 8, 'Inclua a palavra-chave foco na descricao.'),
                check('Slug amigavel', slug.length > 0 && /^[a-z0-9-]+$/.test(slug), 6, 'Use um slug limpo.'),
                check('Conteudo descritivo consistente', details.length >= 250, 8, 'A descricao completa deve ter pelo menos 250 caracteres.'),
                check('Imagem social disponivel', socialImageUrl.length > 0, 6, 'Use uma imagem de compartilhamento ou imagem principal.'),
                check('Canonical configurado ou automatico', canonical.length > 0 || slug.length > 0, 4, 'Defina uma URL canonica ou mantenha o slug correto.'),
                check('Robots indexavel', robots.toLowerCase().indexOf('noindex') === -1, 4, 'Evite noindex em produtos publicados.'),
                check('Palavras-chave suficientes', countKeywords(keywords) >= 3, 6, 'Informe ao menos 3 palavras-chave relevantes.')
            ];

            var score = checks.reduce(function (total, item) {
                return total + (item.passed ? item.points : 0);
            }, 0);
            score = Math.min(100, score);
            var color = score >= 85 ? '#31ce36' : (score >= 70 ? '#1d7af3' : (score >= 40 ? '#ffad46' : '#f3545d'));

            if (scoreValue) scoreValue.textContent = score;
            if (scoreRing) scoreRing.style.background = 'conic-gradient(' + color + ' ' + (score * 3.6) + 'deg, #edf0f4 0deg)';
            setCounter('seo_title', title.length);
            setCounter('meta_description', description.length);

            if (previewTitle) previewTitle.textContent = title || 'Nome do produto';
            if (previewUrl) previewUrl.textContent = canonical || ('{{ url('/product') }}/' + (slug || 'produto'));
            if (previewDescription) previewDescription.textContent = description || 'A descricao SEO do produto sera exibida aqui.';
            if (socialTitle) socialTitle.textContent = value(twitterTitleInput) || value(ogTitleInput) || title || 'Nome do produto';
            if (socialDescription) socialDescription.textContent = value(twitterDescriptionInput) || value(ogDescriptionInput) || description || 'A descricao de compartilhamento sera exibida aqui.';
            if (socialImage) socialImage.style.backgroundImage = socialImageUrl ? 'url("' + socialImageUrl + '")' : '';

            if (checklist) {
                checklist.innerHTML = checks.map(function (item) {
                    return '<div class="seo-check ' + (item.passed ? 'is-ok' : 'is-warning') + '">' +
                        '<i class="fas ' + (item.passed ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i>' +
                        '<span>' + item.label + '</span>' +
                    '</div>';
                }).join('');
            }
        }

        document.querySelectorAll('.js-seo-input, #name, #slug, #details').forEach(function (input) {
            input.addEventListener('input', render);
            input.addEventListener('change', render);
        });
        if (featuredImageInput) {
            featuredImageInput.addEventListener('change', function () {
                setTimeout(render, 300);
            });
        }

        setTimeout(render, 100);
    })();
</script>
