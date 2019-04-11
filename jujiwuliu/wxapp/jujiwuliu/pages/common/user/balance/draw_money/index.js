// jujiwuliu/pages/common/user/balance/draw_money/index.js
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },
    draw_money:function(e){
        var that=this;
        if (!e.detail.value.money){
                wx.showModal({
                    title: '温馨提示',
                    content: '请输入提现金额',
                    showCancel: false,
                    success: function () {

                    }
                });
            return false;
        }
        if (e.detail.value.money > that.data.memberInfo.credit2) {
            wx.showModal({
                title: '温馨提示',
                content: '您当前最多可提现' + that.data.memberInfo.credit2 + '元',
                showCancel: false,
                success: function () {

                }
            });
            return false;
        }

        app.util.request({//获取会员信息
            url: 'entry/wxapp/post_withdraw',
            data: { money:e.detail.value.money},
            method: "POST",
            success: function (res) {
                console.log(res)
                if (res.data.errno==0){
                    wx.showModal({
                        title: '温馨提示',
                        content: res.data.message,
                        showCancel: false,
                        success: function () {
                            that.onShow();
                        }
                    });
                }
                
            }
        })
        


    },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    var that = this;

    var userType = '';
    //var userType = wx.getStorageSync('userType') //不读缓存每次重新获取
    this.pageLoading = !1;//防止多次跳转
    app.util.getUserInfo(function (userInfo) {
      //获取到用户信息后再执行下面的操作
      console.log(userInfo)
      if (typeof (userInfo) != "undefined") {
        app.memberInfo = userInfo;
        that.setData({
          memberInfo: userInfo,
        });
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
            if (!userType) {
              if (info.type == 1) {
                wx.setStorageSync('userType', 'worker')
                app.globalData.userType = 'worker'
                that.setData({
                  userType: 'worker'
                })
              } else {
                wx.setStorageSync('userType', 'issuer')
                app.globalData.userType = 'issuer'
                that.setData({
                  userType: 'issuer'
                })
              }
            }
            //初始化底部导航
            app.util.footer(that);

          },
          fail: function (res) {
            that.onLoad()
            console.log(res)
            return false;
          }
        })
      } else {
        console.log('notlogin')
        //跳转到登陆授权页面
        if (!that.pageLoading) {
          that.pageLoading = !0;
          wx.navigateTo({
            url: '/jujiwuliu/pages/common/auth/index'
          })
        }
      }
    });

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})