/**
 * Виджет для линейного отображения и выбора иерархических структур
 */
function InlineCategoryWidget(wrapperId) {

    this.wrapperId = wrapperId;

    /**
     * Список категорий
     */
    this._categories = [];

    /**
     *  Добавляет категорию в список
     */
    this.addCategory = function(id, parentId, name)
    {
        this._categories.push({'id': id, 'parentId': parentId, 'name': name})
    }

    /**
     * Выбирает и показывает корневую категорию
     */
    this.selectRootCategory = function () {
        this.selectCategory(this._findRootCategory())
    }

    /**
     * Запоминает какая категория выбрана и рендерит виджет
     */
    this.selectCategory = function (categoryId) {
        document.getElementById('inline_category_input').value = categoryId;
        this._showCategory(categoryId)
        this.onSelectCategory(categoryId)
    }

    /**
     * Событие срабатываемое после выбора категории
     */
    this.onSelectCategory = function(categoryId) {}

    /**
     * Находит и возвращает корневую категорию (категория у которой не родителя)
     */
    this._findRootCategory = function () {
        for (var key in this._categories) {
            if (this._categories[key]['parentId'] == null) {
                return this._categories[key]['id']
            }
        }
    }

    /**
     * Отображает конкретную категорию и путь до нее
     *
     * @param categoryId
     */
    this._showCategory = function (categoryId) {
        var html = ''

        // Сначала определяем как добраться до корня от категории
        var path = [categoryId].concat(this._findParentIds(categoryId))

        for (key in path) {
            var category = this._findCategoryById(path[key])

            if (category !== undefined) {
                html = this._renderOneCategory(category) + html
            }
        }
        document.getElementById(this.wrapperId).innerHTML = '<div class="btn-group" role="group">' + html + '</div>';
    }

    /**
     * Генерирует html-код для одной категории
     *
     * @param category
     */
    this._renderOneCategory = function (category) {

        var childHtml = '';
        var children = this.findChildren(category['id'])

        if (children.length > 0) {
            childHtml += '  <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu' + category['id'] +'">';
            childHtml += '    <li role="presentation"><a href="#" role="menuitem" tabindex="-1" onclick="iWidget.selectCategory(' + category['id'] + '); return false;">' + category['name'] + '</a></li>'
            childHtml += '    <li class="divider"></li>'

            for (var key in children) {
                var subItem = children[key]
                childHtml += '    <li role="presentation"><a role="menuitem" tabindex="-1" href="#" onclick="iWidget.selectCategory(' + subItem['id'] + '); return false;">' + subItem['name'] + '</a></li>'
            }

            childHtml += '  </ul>'
        }

        var html = '<div class="btn-group" role="group">'
        html +=    '    <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu' + category['id'] +'" data-toggle="dropdown" aria-expanded="true">'
        html +=    category['name']
        html +=    '        <span class="caret"></span>'
        html +=    '    </button>'
        html +=    childHtml
        html +=    '</div>'

        return html;
    }

    /**
     * Находит и возвращает категорию по ее идентификатору
     *
     * @param categoryId
     * @returns {*}
     */
    this._findCategoryById = function (categoryId) {
        // Находим категорию по ID
        var category;
        for (var key in this._categories) {
            if (this._categories[key]['id'] == categoryId) {
                return this._categories[key];
            }
        }
    }

    /**
     * Находит и возвращает все родительские идентификаторы до корневой директории
     *
     * @param categoryId
     * @returns {Array}
     */
    this._findParentIds = function (categoryId) {
        var ids = []

        // Находим категорию по ID
        var category = this._findCategoryById(categoryId);
        if (category !== undefined) {
            ids.push(category['parentId'])
            ids = ids.concat(this._findParentIds(category['parentId']))
        }

        return ids;
    }

    /**
     * Находит и возвращает дочерние категории
     */
    this.findChildren = function (categoryId) {
        var result = []

        for (var key in this._categories) {
            if (this._categories[key]['parentId'] == categoryId) {
                result.push(this._categories[key])
            }
        }

        return result
    }
}