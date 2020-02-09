const DATATABLE_COMMUNITY_WRAPPER = $('.metronic-datatable-community');

const user = DATATABLE_COMMUNITY_WRAPPER.attr('data-user');
const userRole = DATATABLE_COMMUNITY_WRAPPER.attr('data-user-role');
const allowDelete = user ? Security.isGranted(user, 'admin_community_delete') : false;
const allowMassDelete = user ? Security.isGranted(user, 'admin_community_mass_delete') : false;

const communityOptions = {
    "datatableElement": DATATABLE_COMMUNITY_WRAPPER,
    "search": {
        "input": ".metronic-datatable-community-search"
    },
    "useDefaultTemplates": true,
    "actions": {
        "rowEditRoute": "admin_community_edit",
        "rowEditParams": {
            "community": "row_id"
        },
        "rowDeleteRoute": "admin_community_delete",
        "rowDeleteParams": {
            "community": "row_id"
        },
        "deleteAskModal": true
    },
    "columns": [
        {
            "field": "checkbox",
            "title": "",
            "template": '{{record_id}}',
            "sortable": false,
            "width": 20,
            "textAlign": "center",
            "selector": {
                "class": "m-checkbox--solid m-checkbox--brand"
            }
        },
        {
            "field": "id",
            "title": "Numer",
            "width": 50,
        },
        {
            "field": "name",
            "title": "Nazwa",
            "width": 150,
        },
        {
            "field": "city",
            "title": "Miasto",
            "width": 130,
        },
        {
            "field": "status",
            "title": "Status",
            "width": 100,
        },
        {
            "field": "actions",
            "title": "Akcje",
            "width": 150,
            "overflow": "visible",
            "sortable": false
        }
    ]
};

class CommunityList {
    constructor() {
        const datatable = new DataTable(communityOptions);
        datatable.addTemplateToColumnByName('actions', this._renderDatatableActionsColumn);
        datatable.addTemplateToColumnByName('status', this._renderDatatableStatusColumn);
        this._mdt = datatable.create();
    }

    handleMassRemove() {
        const _self = this;

        $('#mass-remove').on('click', function (event) {
            const $this = $(event.currentTarget);
            event.preventDefault();

            let url = $this.attr('href');

            swal({
                title: "Czy na pewno permanentnie usunąć wspólnoty oraz wszystkie ich powiązania?",
                type: 'warning',
                showCancelButton: !0,
                confirmButtonText: "Tak",
                cancelButtonText: "Nie"
            }).then(function (e) {
                if (typeof e.value !== 'undefined') {
                    const ids = _self._getSelectedIds();
                    ListMassRemove.init(url, ids, 'Usunięto wspólnoty', _self._mdt);
                }
            });
        });
    }

    _renderDatatableActionsColumn(rowData) {
        const editActionButtonTemplate = `
            <a href="${Routing.generate("admin_community_edit", {'community': rowData.record_id})}" 
            class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" 
            title="Edytuj"
            >
                <i class="la la-edit"></i>
            </a>
        `;

        const historyActionButtonTemplate = `
            <a href="${Routing.generate("admin_community_history", {'community': rowData.record_id})}" 
            class="m-portlet__nav-link btn m-btn m-btn--hover-metal m-btn--icon m-btn--icon-only m-btn--pill" 
            title="Historia"
            >
                <i class="la la-clock-o"></i>
            </a>
        `;

        let deleteActionButtonTemplate = '';
        if (userRole === 'ROLE_ADMIN') {
            deleteActionButtonTemplate = `<a href="${Routing.generate("admin_community_delete", {'community': rowData.record_id})}" 
                    data-modal-confirm="true" 
                    data-request-method="DELETE" 
                    data-title="Czy na pewno permanentnie usunąć wspólnotę oraz wszystkie jej powiązania?"
                    class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" 
                    title="Usuń"
                    >
                        <i class="la la-trash"></i>
                    </a>`;
        }

        return editActionButtonTemplate + historyActionButtonTemplate + deleteActionButtonTemplate;
    }

    _renderDatatableStatusColumn(dataRow) {
        switch (dataRow.status) {
            case 'unverified':
                return `<span class="m-badge m-badge--metal m-badge--rounded m-badge--wide">Niezweryfikowana</span>`;
            case 'verified':
                return `<span class="m-badge m-badge--success m-badge--rounded m-badge--wide">Zweryfikowana</span>`;
            case 'official':
                return `<span class="m-badge m-badge--success m-badge--rounded m-badge--wide">Oficjalna</span>`;
        }
    }

    handleMassActionsTrigger() {
        const mdt = this._mdt;

        mdt.on("m-datatable--on-check m-datatable--on-uncheck m-datatable--on-layout-updated", function (t) {
            const qty = mdt.rows(".m-datatable__row--active").nodes().length;

            $("#m_datatable_selected_number").html(qty);

            if (qty > 0) {
                $("#m_datatable_group_action_form").collapse("show");
            } else {
                $("#m_datatable_group_action_form").collapse("hide");
            }
        });
    }

    _getSelectedIds() {
        const ids = this._mdt.rows(".m-datatable__row--active").nodes().find('.m-checkbox--single > [type="checkbox"]')
            .map((t, e) = > {
            return $(e).val()
        }
    )
        ;

        return $.makeArray(ids);
    }

    handleMassChangeStatus() {
        const _self = this;

        $('#change-status .dropdown-item').on('click', function (event) {
            const $this = $(event.currentTarget);
            event.preventDefault();

            let url = $this.attr('href');

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    ids: _self._getSelectedIds()
                }
            }).done(function () {
                _self._mdt.reload();

                swal({
                    type: 'success',
                    title: 'Zmieniono statusy dla zaznaczonych wspólnot',
                    showConfirmButton: false,
                    timer: 1500
                });
            }).fail(function (data) {
                const response = jQuery.parseJSON(data.responseText);

                const title = response.title;
                const text = response.text;

                swal({
                    type: 'error',
                    title: title,
                    text: text,
                    showConfirmButton: true
                });

                _self._mdt.reload();
            });
        });
    }
}

$(document).ready(function () {
    if (DATATABLE_COMMUNITY_WRAPPER.length > 0) {
        const list = new CommunityList(communityOptions);

        list.handleMassRemove();
        list.handleMassActionsTrigger();
        list.handleMassChangeStatus();
    }
});
