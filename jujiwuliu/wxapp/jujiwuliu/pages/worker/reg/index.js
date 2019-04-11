// jujiwuliu/pages/worker/reg/index.js
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  // 提交注册
  reg: function (e) {
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
      var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
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
    // if (!val.vertify) {
    //   wx.showToast({
    //     title: '验证码不能为空',
    //     icon: 'warn',
    //     image: '/jujiwuliu/resource/images/error.png',
    //     duration: 1000
    //   });
    //   return false;
    // }
    if (!val.password) {
      wx.showToast({
        title: '密码不能为空',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      });
      return false;
    } else {
      if (val.password.length < 8 || val.password.length > 16) {
        wx.showToast({
          title: '密码在8到16位',
          icon: 'warn',
          image: '/jujiwuliu/resource/images/error.png',
          duration: 1000
        });
        return false;
      }
    }
    if (!val.repassword) {
      wx.showToast({
        title: '密码不能为空',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      });
      return false;
    } else {
      if (val.repassword != val.password) {
        wx.showToast({
          title: '两次密码不同',
          icon: 'warn',
          image: '/jujiwuliu/resource/images/error.png',
          duration: 1000
        });
        return false;
      }
    }
    app.util.request({
      url: 'entry/wxapp/getreg',
      data: {
        usertype: 'worker',
        mobile: val.mobile,
        password: val.password,
        repeatPassword: val.repassword,
        // mobileCode: val.vertify
      },
      method: "POST",
      success: function (res) {
        console.log(res);
        wx.showToast({
          title: '注册成功',
          icon: 'warn',
          duration: 1000
        });
        wx.setStorageSync('userType', 'worker')
        app.globalData.userType = 'worker';
        wx.reLaunch({
          url: '../login/index'
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