describe ("A dummy test", function(){
    it("Math still works", function(){
        var x = 1+1;
        expect(x).toBe(2);
    });
});

xdescribe("Page load", function(){
   xit("Loads a kitten in the background");
   
   xdescribe("Default options", function(){
      xit("Has a choice of kitten, puppy, or bunny");
      xit("Has kitten selected by default");
      xit("Has a choie of reward levels");
      xit("Has a reward level of 100 words selected by default"); 
   });
   
   xdescribe("With no writing previously saved", function(){
      xdescribe("Because the browser doesn't support local storage", function(){
          xit("Starts with a blank text input");
      });
      
      xdescribe("Because the user just didn't enter any", function(){
          xit("Starts with a blank text input");
      });
   });
   
   xdescribe("If writing was previously saved", function(){
       xit("Retrieves writing from local storage");   
   });
});