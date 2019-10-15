// jujiwuliu/pages/worker/bind/index.js
var app = getApp()
var interval = null
Page({

  /**
   * 页面的初始数据
   */
  data: {
    disabled: false,
    vertifyname: '发送验证码',
    currentTime: 60, //倒计时时间
    mobile: '',
    is_getlocation: app.globalData.is_getlocation,
    is_empower: 0
  },
  getmobile: function (e) {
    var that = this;
    that.setData({
      mobile: e.detail.value
    })
  },
  getrealname: function (e) {
    var that = this
    that.setData({
      realname: e.detail.value
    })
  },
  //获取短信验证码
  getvertify: function (e) {
    var that = this;
    var mobile = that.data.mobile;
    if (!mobile) {
      wx.showToast({
        title: '手机号不能为空',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      });
      return false;
    } else {
      var myreg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
      if (!myreg.test(mobile)) {
        wx.showToast({
          title: '手机格式错误！',
          icon: 'success',
          image: '/jujiwuliu/resource/images/error.png',
          duration: 1000
        })
        return false;
      }
    };
    that.setData({
      disabled: true
    });
    var currentTime = that.data.currentTime;
    console.log(currentTime);
    interval = setInterval(function () {
      currentTime--;
      that.setData({
        vertifyname: currentTime + 's'
      })
      if (currentTime <= 0) {
        clearInterval(interval)
        that.setData({
          vertifyname: '重新发送',
          currentTime: 60,
          disabled: false
        })
      }
    }, 1000);
    app.util.request({
      url: 'entry/wxapp/getvertify',
      data: ({
        mobile: mobile
      }),
      method: "POST",
      success: function (res) {
        console.log(res);
        wx.showToast({
          title: '发送成功',
          icon: 'warn',
          duration: 1000
        });
      },
      fail: function (res) {
        var message = res.data.message;
        wx.showToast({
          title: message,
          icon: 'warn',
          image: '/jujiwuliu/resource/images/error.png',
          duration: 1000
        });
        return false;
      }
    })
  },
  // 提交注册
  bind: function (e) {
    var that = this;
    var val = e.detail.value;
    // 手机号
    if (!val.mobile) {
      wx.showToast({
        title: '手机号不能为空',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      });
      return false;
    } else {
      var myreg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
      if (!myreg.test(e.detail.value.mobile)) {
        wx.showToast({
          title: '手机号错误！',
          icon: 'success',
          image: '/jujiwuliu/resource/images/error.png',
          duration: 1000
        })
        return false;
      }
    }
    //验证码
    if (!val.vertify) {
      wx.showToast({
        title: '验证码不能为空',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      });
      return false;
    }
    //姓名
    if (!val.realname) {
      wx.showToast({
        title: '姓名不能为空',
        icon: 'warn',
        iimage: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      })
      return false
    }
    app.util.request({
      url: 'entry/wxapp/getbind',
      data: {
        type: 1,
        mobile: val.mobile,
        code: val.vertify,
        realname: val.realname
      },
      method: "POST",
      success: function (res) {
        console.log(res);
        wx.setStorageSync('userType', 'worker')
        app.globalData.userType = 'worker';
        wx.showModal({
          'title': '温馨提示',
          'content':'绑定成功',
          'showCancel': false,
          'success': function () {
            
              if (!that.pageLoading) {
                that.pageLoading = !0;
                wx.reLaunch({
                  url: '/jujiwuliu/pages/index/index'
                })
              }
            
          }
        })
       
      },
      fail: function (res) {
        console.log(res)
        var message = res.data.message
        wx.showModal({
          title: '请求失败',
          content: message,
          showCancel: false
        });
        return false;
      }
    })
  },

  //注册
  getEmpower: function(){
    var t = this, e = setInterval(function () {
      wx.getSetting({
        success: function (n) {
          var a = n.authSetting["scope.userInfo"];
          a && (clearInterval(e),wx.setStorageSync('at_empower', 1), t.onShow());
        }
      });
    }, 1e3);
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var at_empower = wx.getStorageSync('at_empower')
    at_empower = at_empower ? at_empower : 0
    this.setData({
      is_empower: at_empower
    })
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
    var that = this
    this.pageLoading = !1;//防止多次跳转
    var at_empower = wx.getStorageSync('at_empower')
    at_empower = at_empower ? at_empower : 0
    this.setData({
      is_empower: at_empower
    })
    if (at_empower == 1){
      app.util.getUserInfo(function (userInfo) {
        //获取到用户信息后再执行下面的操作
        if (userInfo.uid) {
          app.memberInfo = userInfo;
          app.util.request({
            url: 'entry/wxapp/getcenter',
            data: {},
            method: "POST",
            success: function (res) {
              console.log(res);
              var info = res.data.data.info;
              if (info.type){
                if (info.type == 1) {
                  wx.setStorageSync('userType', 'worker')
                  app.globalData.userType = 'worker'
                } else {
                  wx.setStorageSync('userType', 'user')
                  app.globalData.userType = 'user'
                }
                wx.navigateTo({
                  url: '/jujiwuliu/pages/index/index'
                })
              }
            },
            fail: function (res) {
              console.log(res)
              return false;
            }
          })
        } else {
          //跳转到登陆引导页面
          if (!that.pageLoading) {
            that.pageLoading = !0;
            wx.navigateTo({
              url: '/jujiwuliu/pages/common/auth/index'
            })
          }
        }
      });  
    }
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