@php
    $seoItem = $item ?? null;
    $seoScore = old('seo_score', $seoItem->seo_score ?? 0);
    $seoAnalysis = old('seo_analysis', $seoItem->seo_analysis ?? []);
    if (is_string($seoAnalysis)) {
        $seoAnalysis = json_decode($seoAnalysis, true) ?: [];
    }
    $seoTitle = old('seo_title', $seoItem->seo_title ?? '');
    $seoFocusKeyword = old('seo_focus_keyword', $seoItem->seo_focus_keyword ?? '');
    $seoDescription = old('meta_description', $seoItem->meta_description ?? '');
    $seoKeywords = old('meta_keywords', $seoItem->meta_keywords ?? '');
    $seoCanonical = old('seo_canonical_url', $seoItem->seo_canonical_url ?? '');
    $seoRobots = old('seo_robots', $seoItem->seo_robots ?? 'index,follow');
    $ogTitle = old('og_title', $seoItem->og_title ?? '');
    $ogDescription = old('og_description', $seoItem->og_description ?? '');
    $ogImage = old('og_image', $seoItem->og_image ?? '');
    $twitterTitle = old('twitter_title', $seoItem->twitter_title ?? '');
    $twitterDescription = old('twitter_description', $seoItem->twitter_description ?? '');
    $twitterImage = old('twitter_image', $seoItem->twitter_image ?? '');
    $initialSocialImage = $seoItem ? $seoItem->seoSocialImageUrl() : '';
@endphp

<div class="card product-seo-card">
    <div class="card-header">
        <h4 class="card-title mb-0">{{ __('SEO do produto') }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-8">
                <div class="form-group">
                    <label for="seo_focus_keyword">{{ __('Palavra-chave foco') }}</label>
                    <input type="text" name="seo_focus_keyword" id="seo_focus_keyword" class="form-control js-seo-input"
                        placeholder="{{ __('Ex.: moda infantil, sunga infantil, conjunto bebe') }}"
                        value="{{ $seoFocusKeyword }}">
                </div>

                <div class="form-group">
                    <label for="seo_title">{{ __('Titulo SEO') }}</label>
                    <input type="text" name="seo_title" id="seo_title" class="form-control js-seo-input"
                        maxlength="90" placeholder="{{ __('Titulo exibido no Google') }}"
                        value="{{ $seoTitle }}">
                    <small class="form-text text-muted"><span data-seo-count="seo_title">0</span>/70 {{ __('caracteres recomendados') }}</small>
                </div>

                <div class="form-group">
                    <label for="meta_description">{{ __('Meta description') }}</label>
                    <textarea name="meta_description" id="meta_description" class="form-control js-seo-input" rows="4"
                        maxlength="220" placeholder="{{ __('Resumo persuasivo do produto para buscadores') }}">{{ $seoDescription }}</textarea>
                    <small class="form-text text-muted"><span data-seo-count="meta_description">0</span>/160 {{ __('caracteres recomendados') }}</small>
                </div>

                <div class="form-group">
                    <label for="meta_keywords">{{ __('Palavras-chave') }}</label>
                    <input type="text" name="meta_keywords" id="meta_keywords" class="tags js-seo-input"
                        placeholder="{{ __('Informe palavras-chave separadas por virgula') }}"
                        value="{{ $seoKeywords }}">
                </div>

                <div class="form-group">
                    <label for="seo_canonical_url">{{ __('URL canonica') }}</label>
                    <input type="url" name="seo_canonical_url" id="seo_canonical_url" class="form-control js-seo-input"
                        placeholder="{{ __('Deixe em branco para usar a URL do produto') }}"
                        value="{{ $seoCanonical }}">
                </div>

                <div class="form-group">
                    <label for="seo_robots">{{ __('Robots') }}</label>
                    <select name="seo_robots" id="seo_robots" class="form-control js-seo-input">
                        @foreach (['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'] as $robotsOption)
                            <option value="{{ $robotsOption }}" {{ $seoRobots === $robotsOption ? 'selected' : '' }}>{{ $robotsOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="og_title">{{ __('Titulo para compartilhamento') }}</label>
                            <input type="text" name="og_title" id="og_title" class="form-control js-seo-input"
                                value="{{ $ogTitle }}" placeholder="{{ __('Usa o titulo SEO se ficar vazio') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="og_image">{{ __('Imagem para compartilhamento') }}</label>
                            <input type="text" name="og_image" id="og_image" class="form-control js-seo-input"
                                value="{{ $ogImage }}" placeholder="{{ __('URL completa ou nome do arquivo') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="og_description">{{ __('Descricao para compartilhamento') }}</label>
                    <textarea name="og_description" id="og_description" class="form-control js-seo-input" rows="3"
                        placeholder="{{ __('Usa a meta description se ficar vazio') }}">{{ $ogDescription }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="twitter_title">{{ __('Titulo Twitter/X') }}</label>
                            <input type="text" name="twitter_title" id="twitter_title" class="form-control js-seo-input"
                                value="{{ $twitterTitle }}" placeholder="{{ __('Opcional') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="twitter_image">{{ __('Imagem Twitter/X') }}</label>
                            <input type="text" name="twitter_image" id="twitter_image" class="form-control js-seo-input"
                                value="{{ $twitterImage }}" placeholder="{{ __('Opcional') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="twitter_description">{{ __('Descricao Twitter/X') }}</label>
                    <textarea name="twitter_description" id="twitter_description" class="form-control js-seo-input" rows="3"
                        placeholder="{{ __('Opcional') }}">{{ $twitterDescription }}</textarea>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="seo-score-panel">
                    <div class="seo-score-ring" data-seo-score="{{ (int) $seoScore }}">
                        <span id="seo-score-value">{{ (int) $seoScore }}</span>
                    </div>
                    <div>
                        <h5 class="mb-1">{{ __('Pontuacao SEO') }}</h5>
                        <p class="mb-0 text-muted">{{ __('Atualiza em tempo real enquanto voce edita.') }}</p>
                    </div>
                </div>

                <div class="seo-preview-box mt-3">
                    <span class="seo-preview-label">{{ __('Preview Google') }}</span>
                    <h5 id="seo-preview-title">{{ $seoTitle ?: ($seoItem->name ?? __('Nome do produto')) }}</h5>
                    <p id="seo-preview-url">{{ $seoCanonical ?: url('/product/' . ($seoItem->slug ?? 'produto')) }}</p>
                    <p id="seo-preview-description">{{ $seoDescription ?: __('A descricao SEO do produto sera exibida aqui.') }}</p>
                </div>

                <div class="seo-preview-box mt-3">
                    <span class="seo-preview-label">{{ __('Preview social') }}</span>
                    <div class="seo-social-image" id="seo-social-image" @if ($initialSocialImage) style="background-image: url('{{ $initialSocialImage }}')" @endif></div>
                    <h5 id="seo-social-title">{{ $ogTitle ?: ($seoTitle ?: ($seoItem->name ?? __('Nome do produto'))) }}</h5>
                    <p id="seo-social-description">{{ $ogDescription ?: ($seoDescription ?: __('A descricao de compartilhamento sera exibida aqui.')) }}</p>
                </div>

                <div class="seo-checklist mt-3" id="seo-checklist">
                    @foreach ($seoAnalysis as $check)
                        <div class="seo-check {{ !empty($check['passed']) ? 'is-ok' : 'is-warning' }}">
                            <i class="fas {{ !empty($check['passed']) ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                            <span>{{ $check['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
