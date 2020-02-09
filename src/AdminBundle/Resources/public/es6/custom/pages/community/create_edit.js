const select_pl_lang = {
    errorLoading: function () {
        return 'Nie można załadować wyników.';
    },
    inputTooLong: function (args) {
        var overChars = args.input.length - args.maximum;

        return 'Usuń ' + overChars + ' ' + pluralWord(overChars, charsWords);
    },
    inputTooShort: function (args) {
        var remainingChars = args.minimum - args.input.length;

        return 'Podaj przynajmniej ' + remainingChars + ' ' +
            pluralWord(remainingChars, charsWords);
    },
    loadingMore: function () {
        return 'Trwa ładowanie…';
    },
    maximumSelected: function (args) {
        return 'Możesz zaznaczyć tylko ' + args.maximum + ' ' +
            pluralWord(args.maximum, itemsWords);
    },
    noResults: function () {
        return 'Brak wyników';
    },
    searching: function () {
        return 'Trwa wyszukiwanie…';
    }
};

var CommunityCreateEdit = function () {
    var initWidgets = function () {
        $('#community_places').select2({
            placeholder: "Dostępne miejsca",
            language: select_pl_lang,
            noResults: function () {
                return 'Brak wyników';
            }
        });
        $('#community_media').select2({
            placeholder: "Dostępne media",
            language: select_pl_lang,
            noResults: function () {
                return 'Brak wyników';
            }
        });

        $('.user-main-select').select2({
            placeholder: "Wybierz zarządcę",
            language: select_pl_lang,
            noResults: function () {
                return 'Brak wyników';
            }
        });

        $('#community_postalCode').inputmask({mask: "99-999"});
        $('#community_officeAddress_postalCode').inputmask({mask: "99-999"});
        $('#community_nip').inputmask({mask: "999-99-99-999"});

        const accountsRepeaters = $(document).find('[data-content^="community[bankAccountNumber]"]');

        accountsRepeaters.each(function () {
            const input = $(this).find('input');

            input.inputmask({mask: "99 9999 9999 9999 9999 9999 9999"});
        });
    };

    var handleCommunityActions = function () {
        $(document).on('click', '[data-add-user]', function (event) {
            event.preventDefault();
            const collectionHolder = $('#' + $(this).attr('data-target'));
            const prototype = collectionHolder.attr('data-prototype');
            const form = prototype.replace(/__name__/g, collectionHolder.children().length);

            collectionHolder.append(form);

            $("#communities select").select2({
                language: pl_lang,
                placeholder: 'Wybierz...',
                noResults: function () {
                    return 'Brak wyników';
                }
            });

            return false;
        });

        $(document).on('click', '[data-remove-user]', function () {
            $(this).closest('.form-group.m-form__group.row.align-items-center').remove();

            return false;
        });
    };

    var handleRepeater = function () {
        $(document).on('click', '[data-add-repeater]', function (event) {
            event.preventDefault();
            const collectionHolder = $('#' + $(this).attr('data-target'));
            const prototype = collectionHolder.attr('data-prototype');
            const form = prototype.replace(/__name__/g, collectionHolder.children().length);

            collectionHolder.append(form);

            return false;
        });

        $(document).on('click', '[data-remove-repeater]', function () {
            event.preventDefault();

            const isStreetRepeater = $(this).closest('[data-content^="community[street]"]');
            if (isStreetRepeater.length === 0) {
                return $(this).closest('.form-group.m-form__group.row.align-items-center').remove();
            }

            const value = $(this).closest('.form-group.m-form__group.row.align-items-center').find('input').val();

            $.ajax({
                url: Routing.generate("admin_staircase_able_to_delete", {"street": value}),
                method: "GET"
            }).done(() = > {
                return $(this).closest('.form-group.m-form__group.row.align-items-center').remove();
        }).
            fail((data) = > {
                swal({
                         type: 'error',
                         title: "Nie możesz usunąć podanej ulicy.",
                         text: "Ulica jest używana przez klatki, zmień ulicę dla zdefiniowanych " +
            "klatek a następnie usuń ulicę dla wspólnoty",
                showConfirmButton
        :
            true
        })
            ;
        })
            ;

            return false;
        });
    };

    var handleEditDataFormSubmit = function () {
        var form = $('#community-form');
        var submit = false;

        form.validate({
            rules: {
                "community[name]": {
                    required: true,
                    remote: {
                        url: Routing.generate('admin_community_unique_name'),
                        type: 'POST',
                        data: {
                            name: function () {
                                return $('#community_name').val();
                            },
                            name_exception: function () {
                                return $('#community_name').val();
                            }
                        }
                    }
                },
                "community[postalCode]": {
                    postcode_pl: true
                },
                "community[city]": {
                    required: true
                },
                "community[nip]": {
                    nip: true
                },
                "community[regon]": {
                    regon: true
                },
                "community[officeAddress][postalCode]": {
                    postcode_pl: true
                },
                "community[officeAddress][openTo]": {
                    required: false,
                    greaterThanTimeWithNull: {
                        param: function () {
                            return $('#community_officeAddress_openFrom').val();
                        },
                        depends: function () {
                            return $('#community_officeAddress_openTo').val() != '';
                        }
                    }
                }
            }
        });

        form.on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                $(this).find('button[type=submit]').first().trigger('click');
            }
        });

        $('#m_community_submit').click(function (e) {

            if (submit) {
                $(this).addClass('m-loader m-loader--right m-loader--light');

                return;
            }
            e.preventDefault();

            const accountsRepeaters = $(document).find('[data-content^="community[bankAccountNumber]"]');
            const streetsRepeaters = $(document).find('[data-content^="community[street]"]');

            accountsRepeaters.each(function () {
                const input = $(this).find('input');

                input.rules('add', {
                    required: true
                });
            });

            streetsRepeaters.each(function () {
                const input = $(this).find('input');

                input.rules('add', {
                    required: true
                });
            });


            form.validate();

            if (!form.valid()) {
                window.scroll(0, 0);
                return;
            }

            if ($('.community-create-form').length) {
                swal({
                    title: 'Chesz dodać do wspólnoty dane testowe?',
                    type: 'info',
                    showCancelButton: !0,
                    confirmButtonText: "Tak",
                    cancelButtonText: "Nie"
                }).then((e) = > {
                    if(typeof e.value !== 'undefined'
            )
                {
                    $('[name="community[demoData]"]').val(1);
                }
            else
                {
                    $('[name="community[demoData]"]').val(0);
                }
                submit = true;
                $(this).trigger('click');
            })
                ;
            } else {
                submit = true;
                $(this).trigger('click');
            }
        })
    };

    //== Public Functions
    return {
        // public functions
        init: function () {
            initWidgets();
            handleEditDataFormSubmit();
            handleRepeater();
            handleCommunityActions();
        }
    };
}();

$(document).ready(function () {
    if ($('.community-create-form').length || $('.community-edit-form').length) {
        CommunityCreateEdit.init();
    }
});

