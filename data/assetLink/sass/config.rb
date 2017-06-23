# windows ruby: http://rubyinstaller.org/downloads/
# or
# sudo yum install ruby-devel
# 
# gem install sass
# gem install compass
# gem install SassyLists
# @note compass command might not be available for root (sudo)

Encoding.default_external = 'utf-8'

require "SassyLists"
# Require any additional compass plugins here.

# Removing all comments by applying a monkey patch to SASS compiler
#require "../../../lib/bite/code/ruby/bite/sass/remove-all-comments-monkey-patch"

# Set this to the root of your project when deployed:
http_path = "/"
css_dir = "../../../public/assetLink/css/compiled"
sass_dir = "./"
images_dir = "../../../public/assetLink/img"
javascripts_dir = "../../../public/assetLink/js"

# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed
output_style = :compressed

# To enable relative paths to assets via compass helper functions. Uncomment:
# relative_assets = true

# To disable debugging comments that display the original location of your selectors. Uncomment:
# line_comments = false
line_comments = false


# If you prefer the indented syntax, you might want to regenerate this
# project again passing --syntax sass, or you can uncomment this:
# preferred_syntax = :sass
# and then run:
# sass-convert -R --from scss --to sass sass scss && rm -rf sass && mv scss sass
