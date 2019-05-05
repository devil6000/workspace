// jujiwuliu/pages/common/advises/list.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    model: ['投诉','建议'],
  },

  on_loadlist: function () {
    var that = this;
    if (that.data.aloading == 1){
      return false;
    }
    that.setData({
      aloading: 1
    })
    setTimeout(function () {//无论有没有加载完成 2秒后都自动退出加载状态
      that.setData({
        aloading: 0
      })
    }, 2000)

    var page = that.data.page ? that.data.page + 1 : 1;
    app.util.request({
      url: 'entry/wxapp/getAdvisesList',
      cachetime: 0,
      data: { page: page },
      success: function (res) {
        var info = that.data.list ? that.data.list : [];
        if (res.data.data.length > 0){
          var list = res.data.data;
          for(var i = 0; i < res.data.data.length; i++){
            info.push(res.data.data[i])
          }
        }

        that.setData({
          page: page,
          list: info,
          aloading: 0
        })
      }
    })
  },

  onReachBottom: function(){
    this.on_loadlist()
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    app.util.footer(that);
    that.on_loadlist();
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