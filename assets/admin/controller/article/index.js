!function () {
    let table;
    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/admin/api/article/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "封面",
                            name: "cover",
                            type: "image",
                            placeholder: "请选择封面",
                            uploadUrl: '/admin/api/upload/send',
                            photoAlbumUrl: '/admin/api/upload/get',
                            height: 64
                        },
                        {
                            title: "标题",
                            name: "title",
                            type: "input",
                            placeholder: "请输入文章标题",
                            required: true
                        },
                        {
                            title: "摘要",
                            name: "summary",
                            type: "textarea",
                            placeholder: "文章摘要(可留空自动生成)",
                            height: 60
                        },
                        {
                            title: "内容",
                            name: "content",
                            type: "editor",
                            uploadUrl: '/admin/api/upload/send',
                            photoAlbumUrl: '/admin/api/upload/get',
                            placeholder: "请输入文章内容",
                            height: 420
                        },
                        {title: "排序", name: "sort", type: "input", placeholder: "值越小越靠前"},
                        {title: "状态", name: "status", type: "switch", text: "显示|隐藏", default: 1},
                    ]
                }
            ],
            assign: assign,
            autoPosition: true,
            height: "auto",
            width: "800px",
            done: () => {
                table.refresh();
            }
        });
    }

    table = new Table("/admin/api/article/data", "#article-table");
    table.setUpdate("/admin/api/article/save");
    table.setColumns([
        {checkbox: true},
        {field: 'id', title: 'ID', sort: true},
        {field: 'cover', title: '封面', type: "image", style: "border-radius:8px;", width: 48},
        {field: 'title', title: '标题', class: "nowrap"},
        {field: 'views', title: '浏览量', sort: true},
        {field: 'sort', title: '排序(越小越前)', sort: true, type: "input", reload: true},
        {field: 'create_time', title: '创建时间', sort: true},
        {field: 'status', title: '状态', type: "switch", text: "显示|隐藏", reload: true},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'fa-duotone fa-regular fa-pen-to-square',
                    class: "text-primary",
                    click: (event, value, row, index) => {
                        util.post("/admin/api/article/data", {"equal-id": row.id}, res => {
                            let item = res?.data?.list?.find(i => i.id == row.id) || row;
                            modal(util.icon("fa-duotone fa-regular fa-pen-to-square me-1") + " 编辑文章", item);
                        });
                    }
                },
                {
                    icon: 'fa-duotone fa-regular fa-trash-can',
                    class: "text-danger",
                    click: (event, value, row, index) => {
                        message.ask("是否删除此文章？", () => {
                            util.post('/admin/api/article/del', {list: [row.id]}, res => {
                                message.success("删除成功");
                                table.refresh();
                            });
                        });
                    }
                }
            ]
        },
    ]);

    table.setSearch([
        {title: "标题(模糊搜索)", name: "search-title", type: "input"},
        {title: "状态", name: "equal-status", type: "select", dict: "_common_eye", search: true},
        {title: "创建时间", name: "between-create_time", type: "date"},
    ]);
    table.setState("status", "_common_eye");
    table.setPagination(15, [15, 30, 50, 100]);
    table.render();

    $('.btn-app-create').click(function () {
        modal(`<i class="fa-duotone fa-regular fa-circle-plus"></i> 添加文章`);
    });

    $('.btn-app-del').click(() => {
        let data = table.getSelectionIds();
        if (data.length == 0) {
            layer.msg("请至少勾选1个文章进行操作！");
            return;
        }
        message.ask("您确定要删除已经选中的文章吗？这是不可恢复的操作！", () => {
            util.post("/admin/api/article/del", {list: data}, () => {
                message.success("全部删除成功");
                table.refresh();
            });
        });
    });
}();
