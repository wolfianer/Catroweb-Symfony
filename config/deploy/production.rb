# Custom SSH Options
# ==================
# You may pass any option but keep in mind that net/ssh understands a
# limited set of options, consult the Net::SSH documentation.
# http://net-ssh.github.io/net-ssh/classes/Net/SSH.html#method-c-start
#
# Global options
# --------------
set :ssh_options, {
    config: false,
    forward_agent: true
}

# PUBLIC-specific deployment configuration
# please put general deployment config in config/deploy.rb

set :application, "Pocketcode"
set :domain,      "catrobat-share.ist.tugraz.at"
set :deploy_to,   "/var/www/share.catrob.at/"
set :user,        "unpriv"

role :web,        domain
role :app,        domain, :primary => true