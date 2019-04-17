// jujiwuliu/pages/worker/details/index.js
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    data: {},
    //图片
    slide: {
      0:'../../../resource/images/banner.png',
      1: '../../../resource/images/banner.png',
      2: '../../../resource/images/banner.png',
    },
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
    imgheights: [],//不填写则默认获取图片高度（比较耗资源）
    //图片宽度
    imgwidth: 750,
    //默认
    current: 0,
  },

  takeOrder: function(){
    var that = this
    app.util.request({
      url: 'entry/wxapp/gettakeOrder',
      data: {
        id: that.data.data.id
      },
      method: "POST",
      success: function (res) {
        console.log(res);
        wx.showModal({
          title: '通知',
          content: '接单成功',
          showCancel: false,
          success: function (res) {
            var page_opt = getCurrentPages();
            var prev_page = page_opt[page_opt.length - 2];
            prev_page.setData({
              nav_opt:1
            })
            //给上一页传值
            wx.navigateBack();//返回上一页
            // wx.redirectTo({
            //   url: "/jujiwuliu/pages/worker/index/index",
            // });
          }
        });
      },
      fail: function (res) {
        var message = res.data.message;
        wx.showModal({
          title: '获取失败',
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
    var that = this
    //获取地理位置
    app.util.getLocation({
      cachetime: 180,//3分钟更新一次
      success: function (data) {
        console.log(111)
      },
      fail: function (res) {
        console.log(111)

        that.setData({
          locationModelShow: 1,
        });
      }
    });
    var cachedata = wx.getStorageSync('getLocation');
    that.setData({
      options: options,
    });
    app.util.request({
      url: 'entry/wxapp/getlistdetails',
      data: {
          id: options.id,
        rid: options.rid,
        lat: cachedata.data.lat,
        lng: cachedata.data.lng,
      },
      method: "POST",
      success: function (res) {
        console.log(res);
        var data = res.data.data;
        that.setData({
          data: data,
          slide:data.images
        })
      },
      fail: function (res) {
        var message = res.data.message;
        wx.showModal({
          title: '获取失败',
          content: message,
          showCancel: false
        });
        return false;
      }
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
    //获取用户数据
    app.util.request({
      url: 'entry/wxapp/getcenter',
      data: {},
      method: "POST",
      success: function (res) {
        console.log(res);
        var info = res.data.data.info;
        that.setData({
          rest: info.status
        })
      },
      fail: function (res) {
        console.log(res)
        return false;
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

  imageLoad: function (e) {//轮播图高度自适应
    //获取图片真实宽度
    var imgwidth = e.detail.width,
      imgheight = e.detail.height,
      //宽高比
      ratio = imgwidth / imgheight;
    //计算的高度值

    var viewHeight = 750 / ratio;
    var imgheight = viewHeight
    var imgheights = this.data.imgheights
    //把每一张图片的高度记录到数组里
    imgheights.push(imgheight)
    this.setData({
      imgheights: imgheights,
    })
  },
  fulfilOrder:function(){
    var that = this
    //发布方确认打款
    
    app.util.request({
      url: 'entry/wxapp/setreleaseapply',
      data: { id: that.data.data.orderid},
      method: "POST",
      success: function (res) {
        console.log(res);
        wx.showModal({
          title: '温馨提示',
          content: '提交成功，若发布方3天未审核将自动打款！',
        })
      },
      fail: function (res) {
        console.log(res)
        return false;
      }
    })
  },
  //确认打款，修改订单为完成状态
  fulfulPay: function(){
    var that = this;
    wx.showModal({
      title: '温馨提示',
      content: '确认要打款？',
      success: function(res){
        if(res.confirm){
          app.util.request({
            url: 'entry/wxapp/releasePay',
            data: {id: that.data.data.orderid},
            method: "POST",
            success: function(res){
              wx.onLoad(that.data.options);
            }
          })
        }
      }
    })
  },
  bindchange: function (e) {//轮播图无限滚动
    this.setData({ current: e.detail.current })
  },
  callPhone: function(){
    wx.makePhoneCall({
      phoneNumber: this.data.data.jd_mobile,
    })
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