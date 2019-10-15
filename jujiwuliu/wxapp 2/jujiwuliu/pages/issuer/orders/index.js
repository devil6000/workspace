// jujiwuliu/pages/worker/orders/index.js
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    app.util.footer(that);
    this.setData({
      status: 2
    })
    this.list();
  },

  getNav: function (e) {
    var status = e.currentTarget.dataset.index
    this.setData({
      status: status
    })
    this.list()
  }, 
  list: function () {
    var that = this
    app.util.request({
      url: 'entry/wxapp/getlist',
      data: {
        status: that.data.status
      },
      method: "POST",
      success: function (res) {
        var data = res.data.data;
        that.setData({
          list: data.list ? data.list : [],
          status2: data.status2,
          status3: data.status3,
          // status4: data.status4
          
        })
        console.log(data.list)
      },
      fail: function (res) {
        // console.log(res)
        return false;
      }
    })
  },
  //查看细节
  lookdetails: function (e) {
    var that = this;
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/jujiwuliu/pages/worker/details/index?id=' + id
    });
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
    app.util.getUserInfo(function (userInfo) {
      // console.log(userInfo);debugger;
      //获取到用户信息后再执行下面的操作
      if (userInfo.uid) {
        app.memberInfo = userInfo;
        that.setData({
          memberInfo: userInfo,
        });
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