
class DatagridFiltersFormExtension {
    initialize(naja) {
        naja.addEventListener('complete', this.after_form.bind(this));
        naja.addEventListener('before', this.before_form.bind(this));
    }

    after_form(event) {
        let payload = event.detail.payload;

        if (payload === undefined || payload === null || !payload.hasOwnProperty('afterFiltersForm')) {
            return;
        }

        qmandatagrid();
        $('.datagrid').find('.datagridWrapper').show();
        $('.datagrid').find('.spinner').hide();
    }
    before_form(event) {

    }
}
naja.registerExtension(new DatagridFiltersFormExtension());





function qmandatagrid() {
        //paginator
        $('.paginatorItemsPerPage, .searchSelect, .searchDate, .searchRadio').change(function() {
            $('.datagrid').find('.spinner').show();
            $('.datagrid').find('.datagridWrapper').hide();
            $(this).closest('.datagrid').find("input[name='page']").val(1);
            $(this).closest('.datagrid').find('form').first().find('.submit').trigger('click');
        });

        $('.datagrid .delete').click(function(){
            if ( window.confirm('Really delete?')) {
                $('.datagrid').find('.spinner').show();
                $('.datagrid').find('.datagridWrapper').hide();
            } else {
                return false;
            }
        });

        $('.search').keyup(function(){
            $(this).closest('.datagrid').find("input[name='page']").val(1);
        });

        if ($('.datagrid td.actions')) {
            $('.datagrid td').not('.actions').each(function(){
                //$(this).css('cursor', 'pointer');
                //$(this).click(function() {
                //    const href = $(this).parent().find('a').first().attr('href');
                //    window.location = href;
                //});
            })
        }

        $('.datagrid-filter-reset, .datagrid-filter-reset-date').click(function() {
            $('.datagrid').find('.spinner').show();
            $('.datagrid').find('.datagridWrapper').hide();
            $(this).prev().val('');
            $(this).closest('.datagrid').find('form').first().find('.submit').trigger('click');
        })
            $('.datagrid').find('.submit').click(function(){
                $('.datagrid').find('.spinner').show();
                $('.datagrid').find('.datagridWrapper').hide();
            });
    }

    const paginatorAction = function(page, id) {
        $('.datagrid').find('.spinner').show();
        $('.datagrid').find('.datagridWrapper').hide();
        $("#" + id + " input[name='page']").val(page);
        $("#" + id).find('form').first().find('.submit').trigger('click');
    }

    const refresh = function(id) {
        $('.datagrid').find('.spinner').show();
        $('.datagrid').find('.datagridWrapper').hide();
        $("#" + id).find('form').first().find('.submit').trigger('click');
    }

    const reset = function(id) {
        $('.datagrid').find('.spinner').show();
        $('.datagrid').find('.datagridWrapper').hide();
        $("#" + id + " input[name='page']").val(1);
        $("#" + id + " input[name='reset']").val('reset');
        $("#" + id).find('form').first().find('.submit').trigger('click');
    }

    const sorting = function(name, sort, id) {
        $('.datagrid').find('.spinner').show();
        $('.datagrid').find('.datagridWrapper').hide();
        console.log()
        $("#" + id + " input[name='sortColumn']").val(name);
        $("#" + id + " input[name='sortSort']").val(sort);
        $("#" + id).find('form').first().find('.submit').trigger('click');
    }


    $(document).ready(function(){
        qmandatagrid();

    })
