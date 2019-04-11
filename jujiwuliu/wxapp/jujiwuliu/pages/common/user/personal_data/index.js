// jujiwuliu/pages/common/user/personal_data/index.js
var app = getApp();
Page({

    /**
     * Page initial data
     */
    data: {
        tabBar:[]
    },

    /**
     * Lifecycle function--Called when page load
     */
    onLoad: function (options) {
        var that=this;
        app.util.footer(that);

    },

    /**
     * Lifecycle function--Called when page is initially rendered
     */
    onReady: function () {

    },

    /**
     * Lifecycle function--Called when page show
     */
    onShow: function () {
        // 页面显示
        var that = this
        //验证用户
        app.user_verify();
        var userType = '';
        //var userType = wx.getStorageSync('userType') //不读缓存每次重新获取
        this.pageLoading = !1;//防止多次跳转
        app.util.getUserInfo(function (userInfo) {
            //获取到用户信息后再执行下面的操作
            console.log(userInfo)

            if (userInfo && typeof (userInfo) != "undefined" && typeof (userInfo) != "string") {
                app.memberInfo = userInfo;
                that.setData({
                    memberInfo: userInfo,
                });
                console.log(app.memberInfo)
                app.util.request({//获取会员信息
                    url: 'entry/wxapp/getcenter',
                    data: {},
                    method: "POST",
                    success: function (res) {

                        var info = res.data.data.info;
                        that.setData({
                            memberInfo: info
                        })
                        wx.setStorageSync('user_info', info)
                        //初始化底部导航
                        app.util.footer(that);
                    },
                    fail: function (res) {
                        that.onLoad()
                        return false;
                    }
                })
            } else {
                //这里关闭引导页
                that.hideLoading({
                    complete: function () {
                        if (!that.pageLoading) {
                            that.pageLoading = !0;
                            wx.navigateTo({
                                url: '/jujiwuliu/pages/common/auth/index'
                            })
                        }
                    }

                });

            }
        });

    },
    submit:function(e){
        // if (e.detail.value.alipay_real.length==0){
        //     wx.showToast({
        //         title: '手机号码或密码不得为空!',
        //         icon: 'loading',
        //         duration: 1500
        //     })
        //     return false;
        // }
        var data={};
        data.alipay_account = e.detail.value.alipay_account;
        data.alipay_real = e.detail.value.alipay_real;
        data.opening_bank = e.detail.value.opening_bank;
        data.bank_cardid = e.detail.value.bank_cardid;
        data.bank_cardreal = e.detail.value.bank_cardreal;

        app.util.request({//获取会员信息
            url: 'entry/wxapp/MemberEdit',
            data: data,
            method: "POST",
            success: function (res) {
                console.log(res);
                if (res.data.errno==0){
                    wx.showModal({
                        title: '温馨提示',
                        content: '操作成功!',
                        showCancel: false,
                        success:function(){
                            wx.navigateBack({ changed: true });//返回上一页
                        }
                    });
                }
            }
        })

    },
    /**
     * Lifecycle function--Called when page hide
     */
    onHide: function () {

    },

    /**
     * Lifecycle function--Called when page unload
     */
    onUnload: function () {

    },

    /**
     * Page event handler function--Called when user drop down
     */
    onPullDownRefresh: function () {

    },

    /**
     * Called when page reach bottom
     */
    onReachBottom: function () {

    },

    /**
     * Called when user click on the top right corner to share
     */
    onShareAppMessage: function () {

    }
})