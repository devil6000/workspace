// jujiwuliu/pages/common/user/area_manager/apply.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    sexArray: ['保密', '男', '女'],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    app.util.footer(that);
    //选项卡默认选中位置
    wx.getSystemInfo({
      success: function (res) {
        var tabs_dat = that.data.tabs;
        tabs_dat.sliderLeft = (res.windowWidth / that.data.tabs.titles.length - that.data.tabs.sliderWidth) / 3,
          tabs_dat.sliderOffset = res.windowWidth / that.data.tabs.titles.length * that.data.tabs.activeIndex
        that.setData({
          tabs: tabs_dat
        });
      }
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
    var that = this;
    app.util.request({
      url: 'entry/wxapp/getAreaManaager',
      method: 'GET',
      success: function(res){
        that.setData({
          areaManager: res.data.data
        })
      }
    })
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