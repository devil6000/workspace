// jujiwuliu/pages/worker/index/index.js
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    locationModelShow:0,
    list: [],
    status: 1,
    status1: 0,
    status2: 0,
    status3: 0,
    status4: 0,
    rest:0,
    distanceData: {
      modelShowDistance: !1,
      distanceArr: [
        {name: '10公里', value: '10'},
        {name: '20公里', value: '20'},
        {name: '无限制', value: '-1'}
      ]
    }
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
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    app.util.footer(that);

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
  },

  refresh: function(){
    this.list()
  },

  getNav:function(e){
    var status = e.currentTarget.dataset.index
    this.setData({
      status: status
    })
    this.list()
  },

  list: function(){
    var that = this
    var distance = wx.getStorageSync('distanceType')
    app.util.request({
      url: 'entry/wxapp/getlist',
      data: {
        lat:that.data.lat,
        lng: that.data.lng,
        status: that.data.status,
        distance: distance
      },
      method: "POST",
      success: function (res) {
        console.log(res);
        var data = res.data.data;
        that.setData({
          list: data.list ? data.list : [],
          status1: data.status1,
          status2: data.status2,
          status3: data.status3,
          status4: data.status4
        })
      },
      fail: function (res) {
        console.log(res)
        return false;
      }
    })
  },

  setDistance: function(){
    var distance = wx.getStorageSync('distanceType')
    if(distance == ''){
      distance = 10
    }
    var t
    let distanceArr = this.data.distanceData.distanceArr
    for(let i = 0; i < distanceArr.length; i++){
      if (distanceArr[i].value == distance){
        distanceArr[i].checked = true
      }else{
        distanceArr[i].checked = false
      }
    }
    this.setData({
      'distanceData.modelShowDistance': !0,
      'distanceData.distanceArr': distanceArr
    })
  },

  hide_distance: function(){
    this.setData({
      'distanceData.modelShowDistance': !1
    })
  },

  distanceChange: function(e){
    this.setData({
      distanceType: e.detail.value
    })
  },

  confirm_distance: function(){
    var type = this.data.distanceType
    wx.setStorageSync('distanceType', type)
    this.setData({
      'distanceData.modelShowDistance': !1
    })
    this.onShow(this.options)
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },
  bindchange: function (e) {
    // console.log(e.detail.current)
    this.setData({ current: e.detail.current })
  },
  confirmclick: function () {
    this.setData({
      locationModelShow: !1
    }), wx.openSetting({
      success: function (t) { }
    });
  },
  changeRest:function(){
    var that = this
    //获取用户数据
    app.util.request({
      url: 'entry/wxapp/getrest',
      data: {},
      method: "POST",
      success: function (res) {
        console.log(res);
        var status = res.data.data;
        that.setData({
          rest: status
        })
      },
      fail: function (res) {
          console.log(res.data.message )
          wx.showModal({
              title: '温馨提示',
              content: res.data.message ? res.data.message:'操作失败！',
              showCancel: false
          })
        return false;
      }
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function (options) {
    var that = this
    var page_opt = getCurrentPages();//获取上一页传值
    var crrpage = page_opt[page_opt.length - 1];
    if (crrpage.data.nav_opt == 1) {//如果获取到跳转到第一个nav则
      that.setData({
        status: 2
      })
    }

    //获取地理位置
    app.util.getLocation({
    //   cachetime: 180,//3分钟更新一次
      success: function (data) {
          console.log(data)
          
          that.setData({
              lat: data.data.lat,
              lng: data.data.lng,
          })
          //获取数据数据
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
                  console.log(1122112121212)
                  that.list()
              },
              fail: function (res) {
                  console.log(res)
                  return false;
              }
          })
      },
      fail: function (res) {
        that.setData({
          locationModelShow: 1,
        });
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
    //转发事件 带上用户id （加密） 
    var user_info = wx.getStorageSync('user_info');

    return {
      title: '巨吉搬运',
      path: '/jujiwuliu/pages/index/index?introducer=' + app.util.base64_encode(user_info.id),
      // imageUrl:'https://wx.qlogo.cn/mmopen/vi_32/IYXncFLbvfZdjygNiaNyyoQn6yOI8icXZJEYTdibjhfkJKaIUlwTgLe9NZeFsRuJ1Mia7E2wRZXsiaEvy1C00Abiad6Q/132',
    }
  }
})