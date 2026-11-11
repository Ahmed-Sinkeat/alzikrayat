const Joi = require('joi');

const schemas = {
  register: Joi.object({
    firstName: Joi.string().trim().max(50).required(),
    lastName: Joi.string().trim().max(50).required(),
    email: Joi.string().trim().email().required(),
    password: Joi.string().min(6).max(72).required(), // bcrypt ignores bytes past 72
    bio: Joi.string().trim().max(300).allow('').optional(),
  }),
  login: Joi.object({
    email: Joi.string().trim().email().required(),
    password: Joi.string().required(),
  }),
  photo: Joi.object({
    title: Joi.string().trim().max(100).required(),
    description: Joi.string().trim().max(500).allow('').optional(),
  }),
  comment: Joi.object({
    comment: Joi.string().trim().max(500).required(),
  }),
};

// validate(schemaName) -> middleware that checks req.body and replaces it with the cleaned value
function validate(name) {
  return (req, res, next) => {
    const { value, error } = schemas[name].validate(req.body || {}, { abortEarly: false, stripUnknown: true });
    if (error) {
      return res.status(400).json({
        success: false,
        message: error.details.map((d) => d.message).join(', '),
      });
    }
    req.body = value;
    next();
  };
}

module.exports = { validate, schemas };
