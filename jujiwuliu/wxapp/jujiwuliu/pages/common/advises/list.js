// jujiwuliu/pages/common/advises/list.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    model: ['投诉','建议'],
    //是否采用衔接滑动
    circular: true,
    //是否显示画板指示点
    indicatorDots: true,
    //选中点的颜色
    indicatorcolor: "#000",//被css冲突了
    //是否竖直
    vertical: false,
    //是否自动切换
    autoplay: true,
    //滑动动画时长毫秒
    duration: 1000,
    //所有图片的高度
    imgheights: [500],//不填写则默认获取图片高度（比较耗资源）
    //图片宽度
    imgwidth: 750,
    //默认
    current: 0,
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

  imageLoad: function (e) {//轮播图高度自适应
    //获取图片真实宽度
    var imgwidth = e.detail.width,
      imgheight = e.detail.height,
      //宽高比
      ratio = imgwidth / imgheight;
    //计算的高度值

    var viewHeight = this.data.imgwidth / ratio;
    var imgheight = viewHeight
    var imgheights = this.data.imgheights
    //把每一张图片的高度记录到数组里
    imgheights.push(imgheight)
    this.setData({
      imgheights: imgheights,
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
    var setting = wx.getStorageSync('setting_set');
    that.setData({
      setting: setting.data
    })
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