const DATATABLE_COMMUNITY_HISTORY_WRAPPER = $('.metronic-datatable-community-history');

const communityHistoryOptions = {
    "datatableElement": DATATABLE_COMMUNITY_HISTORY_WRAPPER,
    "search": {
        "input": ".metronic-datatable-community-search"
    },
    "useDefaultTemplates": true,
    "actions": {
        "rowEditRoute": "admin_community_edit",
        "rowEditParams": {
            "apartment": "row_id"
        },
        "rowDeleteRoute": "admin_community_delete",
        "rowDeleteParams": {
            "apartment": "row_id"
        },
        "deleteAskModal": true
    },
    "columns": [
        {
            "field": "action",
            "width": 35,
            "title": "Typ",
            "sortable": false
        },
        {
            "field": "logged_at",
            "width": 130,
            "title": "Data",
            "sortable": false
        },
        {
            "field": "blame",
            "width": 150,
            "title": "Użytkownik",
            "sortable": false
        },
        {
            "field": "diff",
            "width": 600,
            "title": "Zdarzenie",
            "sortable": false
        },
        {
            "field": "actions",
            "title": "Akcje",
            "width": 40,
            "overflow": "visible",
            "sortable": false
        }
    ]
};

class CommunityHistoryList {
    constructor() {
        const datatable = new DataTable(communityHistoryOptions);
        datatable.addTemplateToColumnByName('actions', this._renderDatatableActionsColumn);
        datatable.addTemplateToColumnByName('action', this._renderActionColumn);
        datatable.addTemplateToColumnByName('logged_at', this._renderLoggedAtColumn);
        this._mdt = datatable.create();

        DATATABLE_STAIRCASE_HISTORY_WRAPPER.on('m-datatable--on-init', () = > {
            mApp.initTooltips();
    })
        ;
    }

    _renderActionColumn(dataRow) {
        switch (dataRow.action) {
            case 'insert':
                return `<span class="btn btn-success m-btn m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="m-tooltip" data-skin="dark" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Utworzenie"><i class="la la-plus"></i></span>`;
            case 'update':
                return `<span class="btn btn-accent m-btn m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="m-tooltip" data-skin="dark" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Aktualizacja"><i class="la la-wrench"></i></span>`;
            case 'delete':
                return `<span class="btn btn-danger m-btn m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="m-tooltip" data-skin="dark" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Usunięcie"><i class="la la-minus"></i></span>`;
        }
    }

    _renderLoggedAtColumn(dataRow) {
        return moment(dataRow.logged_at).format('DD-MM-YYYY HH:mm:ss');
    }

    _renderDatatableActionsColumn(rowData) {
        return `
            <a href="${Routing.generate("admin_community_history_diff", {'auditLog': rowData.record_id})}" 
            class="m-portlet__nav-link btn m-btn m-btn--hover-metal m-btn--icon m-btn--icon-only m-btn--pill" 
            title="Zobacz zmiany" data-toggle="modal" data-target="#ajax_modal" data-modal-size="modal-lg"
            >
                <i class="la la-random"></i>
            </a>
        `;
    }
}

$(document).ready(function () {
    if (DATATABLE_COMMUNITY_HISTORY_WRAPPER.length > 0) {
        new CommunityHistoryList(communityHistoryOptions);
    }
});
