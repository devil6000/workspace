// jujiwuliu/pages/issuer/map/index.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    subkey: "MF2BZ-SUJCQ-ISK5A-GHYV5-LQU42-KPB3I",
    scale: 19,
    lat: '',
    lng: '',
    markers: [{
      iconPath: "../../../resource/images/location.png",
      id: 0,
      longitude: 0,
      latitude: 0,
      width: 30,
      height: 30,
    }]
  },

  regionchange(e) {

    var that = this
    if (e.type == 'end' && (e.causedBy == 'scale' || e.causedBy == 'drag')){
      var createMap = wx.createMapContext("map", this)
      createMap.getCenterLocation({
        type: 'gcj02',
        success: function(res){
          createMap.translateMarker({
            markerId: 0,
            destination: { longitude: res.longitude, latitude: res.latitude}
          })
          that.setData({
            latitude: res.latitude,
            longitude: res.longitude
          })
        }
      })
    }

    //console.log(e.type)
  },
  markertap(e) {
    console.log(e.markerId)
  },
  controltap(e) {
    console.log(e.controlId)
  },
  goback: function(){
    var pages = getCurrentPages() //获取当前页面参数
    var prevPages = pages[pages.length - 2] //上一页
    prevPages.setData({
      lat: this.data.latitude,
      lng: this.data.longitude
    })
    wx.navigateBack({})
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    var markers = that.data.markers
    markers[0].latitude = options.lat
    markers[0].longitude = options.lng
    that.setData({
      lat: options.lat,
      lng: options.lng,
      markers: markers
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