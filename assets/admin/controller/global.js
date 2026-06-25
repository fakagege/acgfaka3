!function () {
    let _LatestVersion, _LocalVersion, _IsLatestVersion;

    function _LoadStoreUserInfo() {
        util.post({
            url: "/admin/api/app/service",
            loader: false,
            error: false,
            fail: false,
            done: res => {
                const $StoreText = $(`.store-text`);

                if (res?.data?.id <= 0) {
                    return;
                }


                let html = format.badge(`<i class="fa-duotone fa-regular fa-user"></i> ${res.data.username}`, 'a-badge-light edit-store-user');

                if (res.data.level === 0) {
                    html += format.badge(`<i class="fa-duotone fa-regular fa-crown"></i> 專業版`, 'a-badge a-badge-primary hide-mobile');
                }

                if (res.data.level === 1) {
                    html += format.badge(`<i class="fa-duotone fa-regular fa-crown"></i> 企業版`, 'a-badge a-badge-success');
                }

                if (res.data.developer == 1) {
                    html += format.badge(`<i class="fa-duotone fa-regular fa-code"></i> 開發者`, 'a-badge a-badge-success hide-mobile');
                    html += format.badge(`<i class="fa-duotone fa-regular fa-yen-sign"></i> ${res.data.balance}`, 'a-badge a-badge-warning hide-mobile');
                }

                $StoreText.html(format.badgeGroup(html));


                $(`.edit-store-user`).click(() => {
                    component.popup({
                        submit: '/admin/api/app/editPassword',
                        tab: [
                            {
                                name: `<i class="fa-duotone fa-regular fa-user-pen"></i> 修改应用商店账户密码`,
                                form: [
                                    {
                                        title: false,
                                        name: "tips_page",
                                        type: "custom",
                                        complete: (form, dom) => {
                                            dom.html(`<div class="">               
                  <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <p class="mb-0">
                    <i class="fa-duotone fa-regular fa-circle-exclamation"></i> 旧密码输入错误超过10次，将会永久封禁账户，请慎重操作。
                    </p>
                  </div>`);
                                        }
                                    },
                                    {title: false, name: "old_password", type: "password", placeholder: "旧密码"},
                                    {
                                        title: false,
                                        name: "new_password",
                                        type: "input",
                                        placeholder: "新密码(6位字符以上)"
                                    },
                                    {
                                        title: false,
                                        name: "kick",
                                        tips: "如果开启此功能，当您修改密码时，所有已登录服务器将被强制下线，必须使用新密码重新登录。建议仅在账号可能被他人盗用时使用，平时无需勾选。",
                                        type: "switch",
                                        placeholder: "踢掉所有已登录服务器|保持现状"
                                    },
                                ]
                            }
                        ],
                        autoPosition: true,
                        height: "auto",
                        maxmin: false,
                        confirmText: `${util.icon("fa-duotone fa-regular fa-rotate")} 确认修改`,
                        width: "320px"
                    });
                });
            }
        });
    }

    function _AppServerSelect() {
        $('.app-server-select').change(function () {
            util.post("/admin/api/app/setServer", {server: $(this).val()}, res => {
                message.success(res.msg);
            });
        });
    }


    function _Pjax() {
        $(document).pjax('a[target!=_blank]', '#pjax-container', {fragment: '#pjax-container', timeout: 8000});
        $(document).on('pjax:send', function () {
            Loading.show();
        });
        $(document).on('pjax:complete', function () {
            Loading.hide();
        });
        $("a[target!=_blank]").click(function () {
            $('a[target!=_blank]').removeClass("active");
            $(this).addClass("active");
        });
    }

    _LoadStoreUserInfo();
    _AppServerSelect();
    _Pjax();
}();