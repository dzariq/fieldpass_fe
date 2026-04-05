<div class="modal fade" id="playerClubHistoryModal" tabindex="-1" role="dialog" aria-labelledby="playerClubHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="playerClubHistoryModalLabel">{{ __('Club history & performance') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="playerClubHistoryModalLoading" class="text-center py-4 text-muted" style="display:none;">
                    <span class="spinner-border spinner-border-sm" role="status"></span> {{ __('Loading…') }}
                </div>
                <div id="playerClubHistoryModalError" class="alert alert-danger" style="display:none;"></div>
                <div id="playerClubHistoryModalContent" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
