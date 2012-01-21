I wanted to share a quick and easy method for testing Rails migrations when using the MongoDB database.  The flexibility of mongo and ruby makes this pretty straightforward. In this example, we'll be renaming a field in our example "books" collection from "isbn" to "book_number". This is a pretty common type of migration and once you get the hang of this simple case, more complex migrations follow the same pattern. First, lets generate our timestamped migration script boilerplate.

    rails generate migration rename_isbn_to_book_number

Then we'll edit the file that generated under `db/migrate`.  We'll split our code up into several sections as follows.

1. Some class constants shared by all methods
1. The `self.up` method that does the forward migration
1. A helper method to set up some sample data to test forward migration
1. The `self.down` method that does the rollback
1. A helper method to set up some sample data (if needed) to test rollback

First, just define some constants that our methods will use.  We store the mongo update options hash `MultiUpdate` for convenience since most migration update operations want upsert false (don't create any new documents), multi true (update all matching documents), and safe true.

Then we also define a constant for the collection name.  For the real migration, the collection is "books", but for testing, we'll create a new collection called "test_books_migration" as we develop our code.

    class RenameIsbnToBookNumber < Mongoid::Migration
      MultiUpdate = {:upsert => false, :multi => true, :safe=>true}
      Collection = db.collection("books") #Final production code
      Collection = db.collection("test_books_migration") #Just for testing on the console
      def up
      end

      def down
      end
    end

OK, that's our initial boilerplate.  The next step is to take a backup of our development database if there's any data in there we don't want to accidentally wreck.  Then we start coding a little helper method to populate our test collection with fake documents resembling what we expect to see in production, but only focusing on the fields relevant to the migration. Add this method to your migration class.

    def mock_data_for_testing_up
      3.times {|number| Collection.insert({"isbn" => "#{number}"})}
    end

    def show_collection
      Collection.find({}).each {|_| puts _}
    end


This will create 3 dummy documents we can use for testing.  We are putting this code in a method so it's easy to re-run as we tweak and test our migration code.  For complex migrations, many rounds of tweaking to get all the edge cases might be needed.

We can now fire up a rails console and run this code by copying and pasting the 2 class constants and the mock_data_for_testing_up method into the console and then running mock_data_for_testing_up


    $ bundle exec rails console
    Loading development environment (Rails 3.1.1)
    irb(main):001:0> #Paste the following into the console
    MultiUpdate = {:upsert => false, :multi => true, :safe=>true}
    Collection = db.collection("test_books_migration") #Just for testing on the console
    def mock_data_for_testing_up
      3.times {|number| Collection.insert({"isbn" => "#{number}"})}
      Collection.find({}).each {|_| puts _}
    end
    def show_collection
      Collection.find({}).each {|_| puts _}
    end
    irb(main):008:0> mock_data_for_testing_up
    mock_data_for_testing_up
    MONGODB app_development['test_books_migration'].insert([{"isbn"=>"0", :_id=>BSON::ObjectId('4ef5f43b2a4397a5d7000001')}])
    MONGODB app_development['test_books_migration'].insert([{"isbn"=>"1", :_id=>BSON::ObjectId('4ef5f43b2a4397a5d7000002')}])
    MONGODB app_development['test_books_migration'].insert([{"isbn"=>"2", :_id=>BSON::ObjectId('4ef5f43b2a4397a5d7000003')}])
    irb(main):009:0> show_collection
    MONGODB app_development['test_books_migration'].find({})
    {"_id"=>BSON::ObjectId('4ef5f3812a4397a5bd000001'), "isbn"=>"0"}
    {"_id"=>BSON::ObjectId('4ef5f3812a4397a5bd000002'), "isbn"=>"1"}
    {"_id"=>BSON::ObjectId('4ef5f3812a4397a5bd000003'), "isbn"=>"2"}
    => nil


So now we have a separate, well-understood test collection ready to test our simple migration.  Let's code up our `up` method. To do our migration.

    def up
      #We want to rename the book.isbn field to book.book_number
      Collection.find({"isbn" => {"$exists" => 1}}).each do |book|
        update_op = {
          "$unset" => {"isbn" => 1},
          "$set" => {"book_number" => book["isbn"]}
        }
      Collection.update({"_id" => book["_id"]}, update_op, MultiUpdate)
    end

We can paste that into the console and run it to test our migration.  We can verify the results with `show_colletion`.  If we want to test other records for the rollback, we can create a `mock_data_for_testing_down` method.

This should give you a really quick way to experiment and get your migration code working.  Mongo has some advanced query and modify capabilities that can do amazing things, and an easy way to do some trial and error is handy.  If you make a mess of your test data, you can use `Collection.drop` to get a clean slate. Here's the final migration code for reference. **Don't forget** to remove the test collection constant and drop the test collection from your database when your ready to start running your code for real with `rake db:migrate`.

    class RenameIsbnToBookNumber < Mongoid::Migration
      MultiUpdate = {:upsert => false, :multi => true, :safe=>true}
      Collection = db.collection("books") #Final production code

      def up
        #We want to rename the book.isbn field to book.book_number
        Collection.find({"isbn" => {"$exists" => 1}}).each do |book|
          update_op = {
            "$unset" => {"isbn" => 1},
            "$set" => {"book_number" => book["isbn"]}
          }
          Collection.update({"_id" => book["_id"]}, update_op, MultiUpdate)
        end
      end

      def down
        #We want to rename the book.book_number field to book.isbn
        Collection.find({"book_number" => {"$exists" => 1}}).each do |book|
          update_op = {
            "$unset" => {"book_number" => 1},
            "$set" => {"isbn" => book["book_number"]}
          }
          Collection.update({"_id" => book["_id"]}, update_op, MultiUpdate)
        end
      end

      #These methods are not called by the migration.  Just for manual testing
      #by copy/pasting into the console
      #To test (by copy/pasting from here to the console)
      #1. Set the MultiUpdate constant. Adjust "Collection" to be a test collection
      #2. Copy/paste the 2 methods below
      #2. Run mock_data_for_testing_up
      #3. Run the body of self.up
      #3b. Optionally run the body of self.up again to make sure it is idempotent
      def mock_data_for_testing_up
        3.times {|number| Collection.insert({"isbn" => "#{number}"})}
      end

      def show_collection
        Collection.find({}).each {|_| puts _}
      end
    end