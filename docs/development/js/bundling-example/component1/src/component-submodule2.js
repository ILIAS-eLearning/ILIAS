export default {

  public1: function() {
    console.log("Subcomponent 2, public1()");
    il.component3.public1();
    internal();
  }
}

function internal() {
  console.log("internal called");
}