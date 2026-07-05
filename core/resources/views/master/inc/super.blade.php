<ul class="nav">
    <li class="nav-item">
        <a href="{{ route('back.dashboard') }}">
            <i class="fas fa-home"></i>
            <p>{{ __('Dashboard') }}</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('back.analytics.index') }}">
            <i class="fas fa-chart-line"></i>
            <p>{{ __('Analitico') }}</p>
        </a>
    </li>

    <li class="nav-item">
        <a data-toggle="collapse" href="#catalog">
            <i class="fas fa-box-open"></i>
            <p>{{ __('Catalog') }}</p>
            <span class="caret"></span>
        </a>
        <div class="collapse" id="catalog">
            <ul class="nav nav-collapse">
                <li><a class="sub-link" href="{{ route('back.category.index') }}"><span class="sub-item">{{ __('Categories') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.subcategory.index') }}"><span class="sub-item">{{ __('Sub categories') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.childcategory.index') }}"><span class="sub-item">{{ __('Child categories') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.brand.index') }}"><span class="sub-item">{{ __('Brands') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.item.add') }}"><span class="sub-item">{{ __('Add Product') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.item.index') }}"><span class="sub-item">{{ __('All Products') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.item.stock.out') }}"><span class="sub-item">{{ __('Stock Out Products') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.bulk.product.index') }}"><span class="sub-item">{{ __('CSV Import & Export') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.platform.old-products') }}"><span class="sub-item">{{ __('Import Old Products') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.review.index') }}"><span class="sub-item">{{ __('Product Reviews') }}</span></a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item {{ request()->is('orders/*') ? 'submenu' : '' }}">
        <a data-toggle="collapse" href="#sales">
            <i class="fas fa-shopping-cart"></i>
            <p>{{ __('Sales and Orders') }}</p>
            <span class="caret"></span>
        </a>
        <div class="collapse" id="sales">
            <ul class="nav nav-collapse">
                <li class="{{ !request()->input('type') && request()->is('admin/orders') ? 'active' : '' }}"><a class="sub-link" href="{{ route('back.order.index') }}"><span class="sub-item">{{ __('All Orders') }}</span></a></li>
                <li class="{{ request()->input('type') == 'Pending' ? 'active' : '' }}"><a class="sub-link" href="{{ route('back.order.index').'?type=Pending' }}"><span class="sub-item">{{ __('Pending Orders') }}</span></a></li>
                <li class="{{ request()->input('type') == 'In Progress' ? 'active' : '' }}"><a class="sub-link" href="{{ route('back.order.index').'?type=In Progress' }}"><span class="sub-item">{{ __('Progress Orders') }}</span></a></li>
                <li class="{{ request()->input('type') == 'Delivered' ? 'active' : '' }}"><a class="sub-link" href="{{ route('back.order.index').'?type=Delivered' }}"><span class="sub-item">{{ __('Delivered Orders') }}</span></a></li>
                <li class="{{ request()->input('type') == 'Canceled' ? 'active' : '' }}"><a class="sub-link" href="{{ route('back.order.index').'?type=Canceled' }}"><span class="sub-item">{{ __('Canceled Orders') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.transaction.index') }}"><span class="sub-item">{{ __('Transactions') }}</span></a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a data-toggle="collapse" href="#commerce">
            <i class="fas fa-cash-register"></i>
            <p>{{ __('Commercial Operation') }}</p>
            <span class="caret"></span>
        </a>
        <div class="collapse" id="commerce">
            <ul class="nav nav-collapse">
                <li><a class="sub-link" href="{{ route('back.code.index') }}"><span class="sub-item">{{ __('Set Coupons') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.shipping.index') }}"><span class="sub-item">{{ __('Shipping') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.platform.correios') }}"><span class="sub-item">{{ __('Correios Brasil') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.country.index') }}"><span class="sub-item">Países de venda</span></a></li>
                <li><a class="sub-link" href="{{ route('back.state.index') }}"><span class="sub-item">{{ __('State Charge') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.tax.index') }}"><span class="sub-item">{{ __('Tax') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.currency.index') }}"><span class="sub-item">{{ __('Currency') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.setting.payment') }}"><span class="sub-item">{{ __('Payment') }}</span></a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a data-toggle="collapse" href="#customers-support">
            <i class="fas fa-headset"></i>
            <p>{{ __('Customers and Support') }}</p>
            <span class="caret"></span>
        </a>
        <div class="collapse" id="customers-support">
            <ul class="nav nav-collapse">
                <li><a class="sub-link" href="{{ route('back.user.index') }}"><span class="sub-item">{{ __('Customer List') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.ticket.index') }}"><span class="sub-item">{{ __('Manages Tickets') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.platform.whatsapp') }}"><span class="sub-item">{{ __('WhatsApp Floating') }}</span></a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a data-toggle="collapse" href="#marketing">
            <i class="fas fa-bullhorn"></i>
            <p>{{ __('Marketing') }}</p>
            <span class="caret"></span>
        </a>
        <div class="collapse" id="marketing">
            <ul class="nav nav-collapse">
                <li><a class="sub-link" href="{{ route('back.campaign.index') }}"><span class="sub-item">{{ __('Campaign Offer') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.platform.popups') }}"><span class="sub-item">{{ __('Promotional Popups') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.subscribers.index') }}"><span class="sub-item">{{ __('Subscribers List') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.subscribers.announcement') }}"><span class="sub-item">{{ __('Announcement') }}</span></a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a data-toggle="collapse" href="#site-content">
            <i class="fas fa-layer-group"></i>
            <p>{{ __('Site Content') }}</p>
            <span class="caret"></span>
        </a>
        <div class="collapse" id="site-content">
            <ul class="nav nav-collapse">
                <li><a class="sub-link" href="{{ route('back.menu.index') }}"><span class="sub-item">{{ __('Menu Builder') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.homePage') }}"><span class="sub-item">{{ __('Home Page') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.slider.index') }}"><span class="sub-item">{{ __('Sliders') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.service.index') }}"><span class="sub-item">{{ __('Services') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.page.index') }}"><span class="sub-item">{{ __('Manages Pages') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.bcategory.index') }}"><span class="sub-item">{{ __('Blog Categories') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.post.index') }}"><span class="sub-item">{{ __('Blogs') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.fcategory.index') }}"><span class="sub-item">{{ __('Faq Categories') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.faq.index') }}"><span class="sub-item">{{ __('Faqs') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('admin.sitemap.index') }}"><span class="sub-item">{{ __('Sitemap') }}</span></a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a data-toggle="collapse" href="#settings">
            <i class="fas fa-cogs"></i>
            <p>{{ __('Settings') }}</p>
            <span class="caret"></span>
        </a>
        <div class="collapse" id="settings">
            <ul class="nav nav-collapse">
                <li><a class="sub-link" href="{{ route('back.setting.system') }}"><span class="sub-item">{{ __('General Settings') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.setting.section') }}"><span class="sub-item">{{ __('Visibility') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.setting.storage') }}"><span class="sub-item">{{ __('Storage Settings') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.language.index') }}"><span class="sub-item">{{ __('Language') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.setting.social') }}"><span class="sub-item">{{ __('Social Login') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.setting.email') }}"><span class="sub-item">{{ __('Email Settings') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.setting.sms') }}"><span class="sub-item">{{ __('SMS Settings') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.cookie.alert') }}"><span class="sub-item">{{ __('Cookies Alert') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.setting.maintainance') }}"><span class="sub-item">{{ __('Maintainance') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.platform.pwa') }}"><span class="sub-item">PWA</span></a></li>
                <li><a class="sub-link" href="{{ route('back.platform.cron') }}"><span class="sub-item">{{ __('Internal Cron Center') }}</span></a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a data-toggle="collapse" href="#system">
            <i class="fas fa-shield-alt"></i>
            <p>{{ __('System') }}</p>
            <span class="caret"></span>
        </a>
        <div class="collapse" id="system">
            <ul class="nav nav-collapse">
                <li><a class="sub-link" href="{{ route('back.role.index') }}"><span class="sub-item">{{ __('Role') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.staff.index') }}"><span class="sub-item">{{ __('System User') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.system.backup') }}"><span class="sub-item">{{ __('System Backup') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('back.database.backup') }}"><span class="sub-item">{{ __('Database Backup') }}</span></a></li>
                <li><a class="sub-link" href="{{ route('front.cache.clear') }}"><span class="sub-item">{{ __('Cache Clear') }}</span></a></li>
            </ul>
        </div>
    </li>
</ul>
