/**
 * Date: 20.10.2017
 * v_0.0.1.3
 *
 */
/**
 * @todo удалить повторяющиеся строки из функций создания talbe DataTable.
 * ChangeList
 * добавил функции инит и сеттеры и геттеры. То что сейчас использовать неудобно. 19.10.2017
 */

/**
 * Класс для работы с таблицами DataTable.
 * @returns {Table}
 */
function Table() {
    var self = this,
            /**
             * Объект таблица с подключенным DataTable
             * @type Object
             */
            tableDT = {},
            /**
             * Используется для поиска и манипуляцией таблицей методами jquery.
             * @type Jquery object содержит ссылку на таблицу
             */
            _$table = {},
            /**
             * Перечень опций таблицы. Используется при создании таблицы.
             * @type Object
             */
            optionsDT = {};

    /**
     * Перед подключением таблицы. Тут делаем все необходимые действия. В данном случае отключаем
     * вывод ошибок
     * @returns {undefined}
     */
    function middleware() {
        $.fn.dataTable.ext.errMode = 'none';
    }

    /**
     * Инициализация таблицы и всех необходимых данных для нее
     * @param {string} selectorTable селектор таблицы
     * @param {string} typeDT тип создаваемой таблицы 'server'||'client'
     * @param {Object} [options=null] options опции
     * @returns {undefined}
     */
    self.init = function (selectorTable, typeDT, options) {
        middleware();

        setOptionsDT(typeDT, options);
        setTable(selectorTable);

        setTableDT(typeDT);

        $('body')
                .off('click', '.select_all')
                .on('click', '.select_all', function (e) {
                    UpdateTable.checkAllRow(selectorTable, $(this).prop('checked'));
                });

        $(selectorTable + ' tbody')
                .off('click', '.checkbox-datatable')
                .on('click', '.checkbox-datatable', function () {
                    UpdateTable.selectOneRow($(this));
                });

    };

    /**
     * Публичный метод вернуть объект таблицы c подключенной DataTable
     * @returns {Native.tableDT|Table.tableDT|Window.tableDT}
     */
    self.getTableDT = function () {
        return self.tableDT;
    };

    /**
     * Установить объект таблицы найденной в форме.
     * @param {string} selectorTable
     */
    function setTable(selectorTable) {
        self._$table = $(selectorTable);
    }

    /**
     * Получить объект таблицы найденной в форме.
     * @returns {Native._$table|Table._$table|Window._$table}
     */
    function getTable() {
        return self._$table;
    }

    /**
     * Установить опции для таблицы
     * @param {string} typeDT server||client
     * @param {object} options Опции, которые можно переопределить
     options {
     dom,
     aoColumnDefs,
     tableTools,
     language,
     {Boolean} [options.processing=true] options.processing,
     {Boolean} [options.serverSide=true] options.serverSide,
     {Object} options.columns,
     {Object} options.ajax,
     {string} options.ajax.url,
     {string} options.ajax.type,
     {Function} options.ajax.dataSrc
     {boolean} options.autoWidth,
     */
    function setOptionsDT(typeDT, options) {
        options = empty(options) ? {} : options;
        options.dom = empty(options.dom) ? Table.getDom() : options.dom;

        var aoColumnDefs = [
            {'bSortable': false, 'aTargets': [0]}
        ];
		thList = $('th');
		var columns = [];
		$.each(thList, function(index, el){

			columns.push({'data':$(el).attr('data-name')});
		});

        options.aoColumnDefs = empty(options.aoColumnDefs) ? aoColumnDefs : options.aoColumnDefs;

        var tableTools = {
            "sRowSelect": "multi",
            "aButtons": ["select_all", "select_none"]
        };
        options.tableTools = empty(options.tableTools) ? tableTools : options.tableTools;
        var language = {
            "url": "/ego/assets/js/egofoxlab/datatables/Russian.json"
        };
        options.language = empty(options.language) ? language : options.language;
        options.autoWidth = empty(options.autoWidth) ? true : options.autoWidth;

        if (typeDT === 'server') {

            options.processing = true;
            options.serverSide = true;
            if(typeof options.ajax !== 'undefined'){
                self.optionsDT = options;
                return true;
            }
            options.ajax = {
                'url': "/" + options.controllerName + "/getListForDataTable",

                "type": "POST",
                 dataSrc: function (e) {

                    if (typeof e.data !== 'undefined') {
                        for (var i = 0, length = e.data.length; i < length; i++) {
                            var cardId = e.data[i].id;
                            name  = e.data[i].name;
							if(typeof e.data[i].active !== 'undefined'){
								if(e.data[i].active == 'Y'){
									active =   'active';
									nameActive = 'block';
									checked = 'checked';
								} else {
									active =   '';
									nameActive = 'activate';
									checked ='unchecked';
								}

								e.data[i].active = ' <input  type="checkbox" data-action="' + nameActive + '" data-finished-change=0 data-toggle="modal" data-target="#warningModal" '
									+ 'class="checkbox-activate ' + active + '"  ' +checked +'>'
									+ '<label for="checkbox"></label>';
							}
                            e.data[i].checkBox = '<input type="checkbox" class="checkbox-datatable" data-id="' + cardId + '">';
                            e.data[i].name = '<a class="historyAPI-next" data-card-id="' + cardId
                                    + '"data-page="' + options.cardUrl+'/' + cardId + '">'
                                    + name + '</a>';
                            e.data[i].action = '<button data-action="delete"'
                                    + 'data-toggle="modal"'
                                    + 'data-target="#warningModal"'
                                    + 'class="btn btn-xs btn-danger button-delete-program-li" title="Удалить">'
                                    + '<i class="glyphicon glyphicon-trash">'
                                    + '</i></button>';
                        }

                        return e.data;
                    }
                    return false;
                }
            };
        }
        options.columns = columns;
        options.fnCreatedRow = function (nRow, aData, iDataIndex) {
                    $(nRow).attr('data-id', aData.id);
                };
        self.optionsDT = options;
	}

    function getOptionsDT() {
        return self.optionsDT;
    }

    /**
     * Create server side version of DataTable
     * and set tableDT
     * if typeDT == 'server', it will add function reload.
     * @param {string} typeDT  server||client
     */
    function setTableDT(typeDT) {
        self.tableDT = getTable().DataTable(getOptionsDT());
        if (typeDT === 'server') {
            self.tableDT.reload = function () {
                self.tableDT.ajax.reload();
            };
        }
    }



}

Table.dom = 'T<"clear">lfrtip';

Table.setDom = function (dom) {
    this.dom = dom;
};

Table.getDom = function () {
    return this.dom;
};
