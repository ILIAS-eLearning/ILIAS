function ZweiFrames(URI1,F1,URI2,F2)
{
  Frame1=eval("parent."+F1);
  Frame2=eval("parent."+F2);
  Frame1.location.href = URI1;
  Frame2.location.href = URI2;
  
  <!-- UpdateLocatorFrame(); -->
  LocatorFrame=eval("parent.locator");
  LocatorFrame.location.href = "./locator.php";

  alert("Hello world!");
}
