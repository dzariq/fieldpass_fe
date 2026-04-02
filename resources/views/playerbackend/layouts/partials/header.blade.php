<!-- header area start -->
<div class="header-area">
    <div class="row align-items-center">
        <!-- nav and search button -->
        <div class="col-md-6 col-sm-8 clearfix header-left-with-nav">
            <button type="button" class="nav-btn" aria-label="Toggle sidebar">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        <!-- profile info & task notification -->
        <div class="col-md-6 col-sm-4 clearfix">
            <ul class="notification-area pull-right">
                <li id="full-view"><i class="ti-fullscreen"></i></li>
                <li id="full-view-exit"><i class="ti-zoom-out"></i></li>
            </ul>
        </div>
    </div>
</div>
<!-- header area end -->

<style>
    /* Ensure burger menu is always visible and tappable */
    .header-area .nav-btn {
        display: inline-block;
        min-width: 44px;
        min-height: 44px;
        padding: 10px;
        border: none;
        background: transparent;
        vertical-align: middle;
    }
    .header-area .nav-btn span {
        display: block !important;
        width: 22px;
        height: 2px;
        background: #303641 !important;
        margin: 4px 0;
    }
    .header-left-with-nav {
        display: flex;
        align-items: center;
    }
</style>